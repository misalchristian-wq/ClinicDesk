<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/sf8_workbook.php';
set_exception_handler(function(Throwable $e){fwrite(STDERR, 'Integration verification failed: '.$e->getMessage().PHP_EOL);exit(1);});
$project=dirname(__DIR__);
$privateConfig=clinicSecurityConfigPath();
$directory=sys_get_temp_dir().'/clinicdesk_sf8_test_'.bin2hex(random_bytes(6));
$dbPort=random_int(33080,33980);
$apiPort=random_int(42080,42980);
$dbProcess=null; $apiProcess=null; $cloudAssets=[]; $db=null;
$oldConfig=getenv('CLINICDESK_SECURITY_CONFIG');
$oldCa=getenv('CURL_CA_BUNDLE');
putenv('CURL_CA_BUNDLE=C:/xampp/apache/bin/curl-ca-bundle.crt');
function startTestProcess(array $command,string $log){
    $process=proc_open($command,[0=>['pipe','r'],1=>['file',$log,'a'],2=>['file',$log,'a']],$pipes);
    if(!is_resource($process))throw new RuntimeException('Unable to launch an isolated test service.');
    fclose($pipes[0]);
    return $process;
}
function requestTestApi(string $url,array $fields=[],?string $token=null,bool $multipart=false): array {
    $ch=curl_init($url);
    $headers=[];
    if($token)$headers[]='Authorization: Bearer '.$token;
    $body=$multipart?$fields:json_encode($fields);
    if(!$multipart)$headers[]='Content-Type: application/json';
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>$headers,
        CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>100]);
    $response=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);
    $decoded=json_decode($response?:'',true);
    if(!is_array($decoded))throw new RuntimeException('Test API did not return JSON (HTTP '.$status.', cURL '.$error.'): '.substr(strip_tags($response?:''),0,500));
    return ['status'=>$status,'body'=>$decoded];
}
function requestTestGet(string $url,string $token): array {
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]);
    $response=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);
    $decoded=json_decode($response?:'',true);
    if(!is_array($decoded))throw new RuntimeException('Test GET did not return JSON (HTTP '.$status.', cURL '.$error.'): '.substr(strip_tags($response?:''),0,500));
    return ['status'=>$status,'body'=>$decoded];
}
function assertIntegration(bool $ok,string $label):void {
    if(!$ok)throw new RuntimeException('FAIL: '.$label);
    echo 'PASS: '.$label."\n";
}
try {
    mkdir($directory,0700,true);
    mkdir($directory.'/app/api',0700,true);
    mkdir($directory.'/app/lib',0700,true);
    copy($project.'/lib/sf8_detection.php',$directory.'/app/lib/sf8_detection.php');
    $dbDir=$directory.'/mysql';
    $install=startTestProcess(['C:/xampp/mysql/bin/mysql_install_db.exe','--datadir='.$dbDir,'--port='.$dbPort],$directory.'/mysql-install.log');
    if(proc_close($install)!==0)throw new RuntimeException('Unable to initialize the isolated database.');
    $dbProcess=startTestProcess(['C:/xampp/mysql/bin/mysqld.exe','--defaults-file='.$dbDir.'/my.ini','--console','--bind-address=127.0.0.1'],$directory.'/mysql.log');
    mysqli_report(MYSQLI_REPORT_OFF);
    for($attempt=0;$attempt<100;$attempt++){
        $candidate=@new mysqli('127.0.0.1','root','','',$dbPort);
        if(!$candidate->connect_errno){$db=$candidate;break;}
        usleep(200000);
    }
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    if(!$db)throw new RuntimeException('Isolated database did not become ready.');
    $db->query('CREATE DATABASE clinicdesk_sf8_test');$db->select_db('clinicdesk_sf8_test');$db->set_charset('utf8mb4');
    $schema=file_get_contents($project.'/schema.sql');
    foreach(['local_accounts','school_years','sf8_uploads','sf8_student_records','arh_records','deworming_wifa_records','immunization_records','okd_lhas_records','tobacco_control_records'] as $table){
        if(!preg_match('/CREATE TABLE `'.preg_quote($table,'/').'` \(([\s\S]*?)\) ENGINE[^;]*;/',$schema,$match))throw new RuntimeException('Missing test table schema.');
        $db->query($match[0]);
        preg_match('/^\s*`([^`]+)`/',$match[1],$column);
        $db->query('ALTER TABLE `'.$table.'` ADD PRIMARY KEY (`'.$column[1].'`), MODIFY `'.$column[1].'` int(11) NOT NULL AUTO_INCREMENT');
    }
    $db->query('ALTER TABLE sf8_student_records ADD UNIQUE KEY unique_lrn_school_year (lrn, school_year)');
    $db->query('ALTER TABLE arh_records ADD UNIQUE KEY idx_unique_arh (lrn, school_year)');
    $db->query('ALTER TABLE deworming_wifa_records ADD UNIQUE KEY idx_unique_deworming_wifa (lrn, school_year)');
    $db->query('ALTER TABLE tobacco_control_records ADD UNIQUE KEY idx_unique_tobacco (lrn, school_year)');
    $db->query('ALTER TABLE immunization_records ADD UNIQUE KEY idx_unique_immunization (lrn, school_year, vaccine, dose)');
    $db->query('ALTER TABLE okd_lhas_records ADD UNIQUE KEY idx_unique_okd_lhas (lrn, school_year, screening_type)');
    $db->query('ALTER TABLE sf8_student_records ADD CONSTRAINT sf8_test_upload_fk FOREIGN KEY (upload_id) REFERENCES sf8_uploads(upload_id) ON DELETE CASCADE');
    foreach(explode(';',file_get_contents($project.'/migrations/20261003_wifa_monitoring.sql')) as $statement){
        if(trim($statement)!=='')$db->query($statement);
    }
    foreach(explode(';',file_get_contents($project.'/migrations/20261003_feeding_progress.sql')) as $statement){
        if(trim($statement)!=='')$db->query($statement);
    }
    foreach(explode(';',file_get_contents($project.'/migrations/20261004_consultations.sql')) as $statement){
        if(trim($statement)!=='')$db->query($statement);
    }
    $db->query("INSERT INTO school_years(year_label,is_active) VALUES('2026-2027',1)");
    $password=bin2hex(random_bytes(12));$hash=password_hash($password,PASSWORD_DEFAULT);
    $stmt=$db->prepare("INSERT INTO local_accounts(full_name,email,password_hash,role,status) VALUES('SF8 Test Nurse','nurse@example.invalid',?,'Clinic Nurse','Active'),('SF8 Test Admin','admin@example.invalid',?,'School Admin','Active')");
    $stmt->bind_param('ss',$hash,$hash);$stmt->execute();$stmt->close();
    $files=['auth.php','bootstrap.php','local_login.php','sf8_crypto.php','sf8_storage.php','sf8_workbook.php','upload_sf8.php','upload_sf8_local.php','save_sf8_upload.php','parse_sf8_from_upload.php','approve_sf8_upload.php','approve_local_upload.php','add_manual_student.php','add_category_record.php','update_category_records.php','update_student_profile.php','health_program_monitoring.php','feeding_monitoring.php','get_program_monitoring.php','get_deworming_wifa_raw.php','get_table1_deworming_wifa_report.php','save_consultation.php','get_consultations.php','get_students_for_consult.php','record_categories.php','who_classifier.php','student_sections.php','sf8_conflicts.php','sf8_approval_validation.php','sf8_parser.php','deworming_wifa_parser.php','okd_lhas_parser.php','immunization_parser.php','tobacco_parser.php','arh_parser.php'];
    foreach($files as $file){
        $code=file_get_contents($project.'/api/'.$file);
        $code=str_replace(['__DIR__ . "/../vendor/autoload.php"',"__DIR__ . '/../vendor/autoload.php'"],var_export($project.'/vendor/autoload.php',true),$code);
        file_put_contents($directory.'/app/api/'.$file,$code);
    }
    copy($project.'/api/who_reference.json',$directory.'/app/api/who_reference.json');
    copy($project.'/api/who_hfa_reference.json',$directory.'/app/api/who_hfa_reference.json');
    $connection='<?php $conn=new mysqli("127.0.0.1","root","","clinicdesk_sf8_test",'.$dbPort.'); $conn->set_charset("utf8mb4");';
    file_put_contents($directory.'/app/api/db.php',$connection);file_put_contents($directory.'/app/db.php',$connection);
    putenv('CLINICDESK_SECURITY_CONFIG='.$privateConfig);
    $apiProcess=startTestProcess([PHP_BINARY,'-d','sys_temp_dir='.sys_get_temp_dir(),'-d','upload_tmp_dir='.sys_get_temp_dir(),'-S','127.0.0.1:'.$apiPort,'-t',$directory.'/app'],$directory.'/api.log');
    usleep(800000);$base='http://127.0.0.1:'.$apiPort.'/api/';
    $login=requestTestApi($base.'local_login.php',['email'=>'nurse@example.invalid','password'=>$password,'role'=>'Clinic Nurse']);
    assertIntegration($login['status']===200 && !empty($login['body']['token']),'local login issues a signed nurse token');
    $token=$login['body']['token'];
    $admin=requestTestApi($base.'local_login.php',['email'=>'admin@example.invalid','password'=>$password,'role'=>'School Admin']);
    $profileDenied=requestTestApi($base.'update_student_profile.php',['record_id'=>1,'learner_name'=>'Unauthorized'],$admin['body']['token']);
    assertIntegration($profileDenied['status']===403,'school admin cannot edit a learner profile through the monitoring link');
    $denied=requestTestApi($base.'parse_sf8_from_upload.php?upload_id=1',[],$admin['body']['token']);
    assertIntegration($denied['status']===403,'a signed School Admin token cannot decrypt nurse previews');
    $book=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$sheet=$book->getActiveSheet()->setTitle('Nutritional Status');
    foreach(['A1'=>'comprehensive_tobacco_control','E5'=>'Encryption Test School','O7'=>'School Year','P7'=>'2026-2027','F7'=>'Grade 7',
        'B9'=>'LRN','C9'=>"Learner's Name",'G9'=>'Sex','H9'=>'Birthdate','I9'=>'Age','J9'=>'Weight (kg)','K9'=>'Height (m)','N10'=>'BMI Category','O9'=>'Height for Age (HFA)',
        'B11'=>'999999999991','C11'=>'Encryption Test Learner','G11'=>'Male','H11'=>'2013-01-01','I11'=>13,'J11'=>40,'K11'=>1.5,'L11'=>2.25,'M11'=>17.78,'N11'=>'Normal','O11'=>'Tall'] as $cell=>$value)$sheet->setCellValue($cell,$value);
    $fixture=$directory.'/synthetic.xlsx';(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($fixture);$book->disconnectWorksheets();
    $upload=requestTestApi($base.'upload_sf8.php',['file'=>new CURLFile($fixture,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','SyntheticSF8.xlsx')],$token,true);
    if(empty($upload['body']['success']))throw new RuntimeException('Upload test failed: '.($upload['body']['message']??'unknown'));
    $id=(int)$upload['body']['upload_id'];
    $cloudAsset=$db->query('SELECT * FROM sf8_uploads WHERE upload_id='.$id)->fetch_assoc();
    $cloudAssets[]=$cloudAsset;
    assertIntegration($cloudAsset['status']==='Pending' && $cloudAsset['file_type']===SF8_ENCRYPTED_TYPE
        && $cloudAsset['report_code']==='students_information', 'wrong A1 is ignored; detected Student Information upload creates an encrypted Pending row in MySQL');
    assertIntegration((int)$db->query('SELECT COUNT(*) AS n FROM sf8_student_records')->fetch_assoc()['n']===0,'upload saves no learner records before approval');
    $preview=requestTestApi($base.'parse_sf8_from_upload.php?upload_id='.$id,[],$token);
    assertIntegration(!empty($preview['body']['success']) && count($preview['body']['records'])===1,'authenticated nurse preview decrypts the actual stored Cloudinary file');
    assertIntegration(($preview['body']['records'][0]['height_for_age']??null)==='Normal','nurse preview shows the calculated WHO height-for-age before approval');
    assertIntegration((int)$db->query('SELECT COUNT(*) AS n FROM sf8_student_records')->fetch_assoc()['n']===0,'preview saves no learner records before approval');
    $approval=requestTestApi($base.'approve_local_upload.php',['upload_id'=>$id,'records'=>[['learner_name'=>'FORGED BROWSER RECORD']]],$token);
    if(empty($approval['body']['success']))throw new RuntimeException('Approval test failed: '.($approval['body']['message']??'unknown'));
    $record=$db->query('SELECT learner_name,lrn,upload_id,school_year,height_for_age FROM sf8_student_records')->fetch_assoc();
    assertIntegration($record && $record['learner_name']==='Encryption Test Learner' && $record['lrn']==='999999999991' && (int)$record['upload_id']===$id,'approval stores the decrypted learner record and ignores forged browser rows');
    assertIntegration($record['height_for_age']==='Normal','SF8 approval calculates WHO height-for-age instead of trusting the spreadsheet label');
    $state=$db->query('SELECT status FROM sf8_uploads WHERE upload_id='.$id)->fetch_assoc();
    assertIntegration($state['status']==='Approved','approval updates the encrypted upload to Approved');
    $repeat=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$id],$token);
    assertIntegration(empty($repeat['body']['success']) && (int)$db->query('SELECT COUNT(*) AS n FROM sf8_student_records')->fetch_assoc()['n']===1,'repeat approval cannot create duplicate learner records');
    $arhBook=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$arhSheet=$arhBook->getActiveSheet()->setTitle('Nutritional Status');
    foreach(['A1'=>'students_information','O7'=>'School Year','P7'=>'2026-2027','F7'=>'Grade 8',
        'B9'=>'LRN','C9'=>"Learner's Name",'G9'=>'Sex','H9'=>'Birthdate','I9'=>'Age',
        'J9'=>'Pregnancy Status','K9'=>'Delivery Mode','L9'=>'Peer Educator',
        'B11'=>'999999999992','C11'=>'Category First Learner','G11'=>'Female','H11'=>'2012-01-01','I11'=>14,
        'J11'=>'Not Pregnant','K11'=>'In School','L11'=>1] as $cell=>$value)$arhSheet->setCellValue($cell,$value);
    $arhFixture=$directory.'/arh-first.xlsx';(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($arhBook))->save($arhFixture);$arhBook->disconnectWorksheets();
    $arhUpload=requestTestApi($base.'upload_sf8_local.php',['file'=>new CURLFile($arhFixture,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ArhFirst.xlsx')],$token,true);
    if(empty($arhUpload['body']['success']))throw new RuntimeException('Category-first upload failed: '.($arhUpload['body']['message']??'unknown'));
    $arhId=(int)$arhUpload['body']['upload_id'];
    $cloudAssets[]=$db->query('SELECT * FROM sf8_uploads WHERE upload_id='.$arhId)->fetch_assoc();
    assertIntegration($arhUpload['body']['report_code']==='adolescent_reproductive_health_arh', 'category-first form type is detected from its columns');
    $arhApproval=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$arhId],$token);
    if(empty($arhApproval['body']['success']))throw new RuntimeException('Category-first approval failed: '.($arhApproval['body']['message']??'unknown'));
    $provisional=$db->query("SELECT record_id, profile_status FROM sf8_student_records WHERE lrn='999999999992'")->fetch_assoc();
    $arhRow=$db->query("SELECT student_record_id FROM arh_records WHERE lrn='999999999992'")->fetch_assoc();
    assertIntegration($provisional && $provisional['profile_status']==='Provisional' && (int)$arhRow['student_record_id']===(int)$provisional['record_id'],
        'ARH-first approval creates one linked provisional learner');
    $blankSections=0;
    foreach(['deworming_wifa_records','okd_lhas_records','immunization_records','tobacco_control_records'] as $table)
        $blankSections+=(int)$db->query("SELECT COUNT(*) AS n FROM `$table` WHERE lrn='999999999992'")->fetch_assoc()['n'];
    assertIntegration($blankSections===0,'category-first approval creates no empty health sections');

    $overrideBook=\PhpOffice\PhpSpreadsheet\IOFactory::load($arhFixture);
    $overrideBook->getActiveSheet()->setCellValue('J11','Pregnant');
    $overrideFixture=$directory.'/arh-override.xlsx';(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($overrideBook))->save($overrideFixture);$overrideBook->disconnectWorksheets();
    $overrideUpload=requestTestApi($base.'upload_sf8_local.php',['file'=>new CURLFile($overrideFixture,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ArhOverride.xlsx')],$token,true);
    if(empty($overrideUpload['body']['success']))throw new RuntimeException('ARH override upload failed: '.($overrideUpload['body']['message']??'unknown'));
    $overrideId=(int)$overrideUpload['body']['upload_id'];
    $cloudAssets[]=$db->query('SELECT * FROM sf8_uploads WHERE upload_id='.$overrideId)->fetch_assoc();
    $review=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$overrideId],$token);
    assertIntegration($review['status']===409 && !empty($review['body']['requires_override']) && !empty($review['body']['conflicts'][0]['changes']),
        'changed ARH data requires nurse comparison before any write');
    $confirmed=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$overrideId,'override_existing'=>true,
        'conflict_fingerprint'=>$review['body']['conflict_fingerprint']],$token);
    if(empty($confirmed['body']['success']))throw new RuntimeException('Confirmed ARH override failed: '.($confirmed['body']['message']??'unknown'));
    $arhState=$db->query("SELECT COUNT(*) AS n, MAX(pregnancy_status) AS value FROM arh_records WHERE lrn='999999999992'")->fetch_assoc();
    assertIntegration((int)$arhState['n']===1 && $arhState['value']==='Pregnant',
        'confirmed ARH override updates the existing row without duplication');

    $identityBook=\PhpOffice\PhpSpreadsheet\IOFactory::load($arhFixture);
    $identityBook->getActiveSheet()->setCellValue('B11','999999999993');
    $identityFixture=$directory.'/arh-lrn-mismatch.xlsx';(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($identityBook))->save($identityFixture);$identityBook->disconnectWorksheets();
    $identityUpload=requestTestApi($base.'upload_sf8_local.php',['file'=>new CURLFile($identityFixture,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ArhLrnMismatch.xlsx')],$token,true);
    if(empty($identityUpload['body']['success']))throw new RuntimeException('LRN mismatch upload failed: '.($identityUpload['body']['message']??'unknown'));
    $identityId=(int)$identityUpload['body']['upload_id'];
    $cloudAssets[]=$db->query('SELECT * FROM sf8_uploads WHERE upload_id='.$identityId)->fetch_assoc();
    $identityReview=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$identityId],$token);
    assertIntegration($identityReview['status']===409 && count($identityReview['body']['identity_conflicts']??[])>0
        && (int)$db->query("SELECT COUNT(*) AS n FROM sf8_student_records WHERE lrn='999999999993'")->fetch_assoc()['n']===0,
        'same-name different-LRN upload is held for review without creating another learner');

    $studentBook=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$studentSheet=$studentBook->getActiveSheet()->setTitle('Nutritional Status');
    foreach(['E5'=>'Encryption Test School','O7'=>'School Year','P7'=>'2026-2027','F7'=>'Grade 8',
        'B9'=>'LRN','C9'=>"Learner's Name",'G9'=>'Sex','H9'=>'Birthdate','I9'=>'Age',
        'J9'=>'Weight (kg)','K9'=>'Height (m)','N10'=>'BMI Category','O9'=>'Height for Age (HFA)',
        'B11'=>'999999999992','C11'=>'Category First Learner','G11'=>'Female','H11'=>'2012-01-01','I11'=>14,
        'J11'=>45,'K11'=>1.55,'L11'=>2.4025,'M11'=>18.73,'N11'=>'Normal'] as $cell=>$value)$studentSheet->setCellValue($cell,$value);
    $studentFixture=$directory.'/student-after-arh.xlsx';(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($studentBook))->save($studentFixture);$studentBook->disconnectWorksheets();
    $studentUpload=requestTestApi($base.'upload_sf8_local.php',['file'=>new CURLFile($studentFixture,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','StudentAfterArh.xlsx')],$token,true);
    if(empty($studentUpload['body']['success']))throw new RuntimeException('Student Information upload failed: '.($studentUpload['body']['message']??'unknown'));
    $studentId=(int)$studentUpload['body']['upload_id'];
    $cloudAssets[]=$db->query('SELECT * FROM sf8_uploads WHERE upload_id='.$studentId)->fetch_assoc();
    $completeApproval=requestTestApi($base.'approve_sf8_upload.php',['upload_id'=>$studentId],$token);
    if(empty($completeApproval['body']['success']))throw new RuntimeException('Provisional completion failed: '.($completeApproval['body']['message']??'unknown'));
    $completed=$db->query("SELECT record_id, profile_status, sex FROM sf8_student_records WHERE lrn='999999999992'")->fetch_assoc();
    assertIntegration((int)$completed['record_id']===(int)$provisional['record_id'] && $completed['profile_status']==='Complete' && $completed['sex']==='Female',
        'Student Information completes the same provisional learner row');

    $consultStudentId=(int)$completed['record_id'];
    $uploadCount=(int)$db->query('SELECT COUNT(*) AS n FROM sf8_uploads')->fetch_assoc()['n'];
    $screeningCount=(int)$db->query('SELECT COUNT(*) AS n FROM okd_lhas_records')->fetch_assoc()['n'];
    $adminConsult=requestTestApi($base.'save_consultation.php',['record_id'=>$consultStudentId,'symptoms'=>'Headache'],$admin['body']['token']);
    assertIntegration($adminConsult['status']===403,'school admin cannot save a nurse consultation');
    $emptyConsult=requestTestApi($base.'save_consultation.php',['record_id'=>$consultStudentId,'care_given'=>'Rest'], $token);
    assertIntegration($emptyConsult['status']===422,'consultation requires reported symptoms or a reason for visit');
    $savedConsult=requestTestApi($base.'save_consultation.php',['record_id'=>$consultStudentId,
        'symptoms'=>'Headache','care_given'=>'Observed and contacted guardian','follow_up_date'=>'2026-10-05','notes'=>'Learner rested'], $token);
    $consultHistory=requestTestGet($base.'get_consultations.php?record_id='.$consultStudentId,$token);
    assertIntegration(!empty($savedConsult['body']['success']) && count($consultHistory['body']['consultations'])===1
        && $consultHistory['body']['consultations'][0]['care_given']==='Observed and contacted guardian',
        'nurse consultation saves observations and care and can be read back');
    assertIntegration((int)$db->query('SELECT COUNT(*) AS n FROM sf8_uploads')->fetch_assoc()['n']===$uploadCount
        && (int)$db->query('SELECT COUNT(*) AS n FROM okd_lhas_records')->fetch_assoc()['n']===$screeningCount,
        'consultation does not create an SF8 upload or inflate OKD/LHAS counts');

    $manualCategory=requestTestApi($base.'add_category_record.php',['category'=>'arh','school_year'=>'2026-2027',
        'record'=>['lrn'=>'999999999994','learner_name'=>'Manual Category First','sex'=>'Male',
            'pregnancy_status'=>'Not Pregnant','peer_educator'=>0]],$token);
    if(empty($manualCategory['body']['success']))throw new RuntimeException('Manual category-first entry failed: '.($manualCategory['body']['message']??'unknown'));
    $manualProvisional=$db->query("SELECT record_id,profile_status FROM sf8_student_records WHERE lrn='999999999994'")->fetch_assoc();
    assertIntegration($manualProvisional && $manualProvisional['profile_status']==='Provisional',
        'manual health entry creates one provisional learner');
    $duplicateManualCategory=requestTestApi($base.'add_category_record.php',['category'=>'arh','school_year'=>'2026-2027',
        'record'=>['lrn'=>'999999999994','learner_name'=>'Manual Category First','sex'=>'Male',
            'pregnancy_status'=>'Not Pregnant','peer_educator'=>0]],$token);
    assertIntegration($duplicateManualCategory['status']===409,'duplicate manual health entry returns a conflict');
    $manualComplete=requestTestApi($base.'add_manual_student.php',['lrn'=>'999999999994','learner_name'=>'Manual Category First',
        'sex'=>'Male','age'=>'13','weight_kg'=>42,'height_m'=>1.5,'school_year'=>'2026-2027',
        'grade_level'=>'8','section'=>'A'],$token);
    if(empty($manualComplete['body']['success']))throw new RuntimeException('Manual learner completion failed: '.($manualComplete['body']['message']??'unknown'));
    $manualState=$db->query("SELECT record_id,profile_status,height_for_age FROM sf8_student_records WHERE lrn='999999999994'")->fetch_assoc();
    assertIntegration((int)$manualState['record_id']===(int)$manualProvisional['record_id'] && $manualState['profile_status']==='Complete',
        'manual Student Information completes the category-first learner without a second row');
    assertIntegration($manualState['height_for_age']==='Normal','manual learner entry saves WHO height-for-age');
    $masterId=(int)$db->query("SELECT record_id FROM sf8_student_records WHERE lrn='999999999991'")->fetch_assoc()['record_id'];
    $db->query("INSERT INTO arh_records(lrn,learner_name,school_year,pregnancy_status) VALUES('888888888881','Encryption Test Learner','2026-2027','Not Pregnant')");
    $historicalId=$db->insert_id;
    $wrongIdentity=requestTestApi($base.'update_category_records.php',['category'=>'arh','rows'=>[[
        'arh_record_id'=>$historicalId,'lrn'=>'999999999994','learner_name'=>'Encryption Test Learner']]],$token);
    assertIntegration($wrongIdentity['status']===409,'historical category LRN cannot be reassigned to a different learner');
    $correctIdentity=requestTestApi($base.'update_category_records.php',['category'=>'arh','rows'=>[[
        'arh_record_id'=>$historicalId,'lrn'=>'999999999991','learner_name'=>'Encryption Test Learner']]],$token);
    $corrected=$db->query('SELECT lrn,student_record_id FROM arh_records WHERE arh_record_id='.$historicalId)->fetch_assoc();
    assertIntegration(!empty($correctIdentity['body']['success']) && $corrected['lrn']==='999999999991'
        && (int)$corrected['student_record_id']===$masterId,
        'historical category LRN correction links the existing master without creating another row');

    $femaleId=(int)$completed['record_id'];
    $db->query("INSERT INTO deworming_wifa_records(upload_id,student_record_id,lrn,learner_name,sex,school_year,grade_level,dewormed_sbfp,wifa,wifa_date)
        VALUES($arhId,$femaleId,'999999999992','Category First Learner','Female','2026-2027','8',1,1,'2026-07-15')");
    $deniedMonitoring=requestTestGet($base.'health_program_monitoring.php?record_id='.$femaleId,$admin['body']['token']);
    assertIntegration($deniedMonitoring['status']===403,'school admin cannot read nurse WIFA monitoring');
    $baseline=requestTestGet($base.'health_program_monitoring.php?record_id='.$femaleId,$token);
    assertIntegration(!empty($baseline['body']['success']) && $baseline['body']['sf8_baseline']['wifa_date']==='2026-07-15',
        'SF8 starting date remains visible as the imported baseline');
    $db->query("INSERT INTO sf8_student_records(lrn,school_year,learner_name,sex,grade_level,profile_status)
        VALUES('999999999995','2026-2027','Unknown Date Learner','Female','Grade 8','Complete')");
    $unknownStudentId=$db->insert_id;
    $db->query("INSERT INTO deworming_wifa_records(upload_id,student_record_id,lrn,learner_name,sex,school_year,grade_level,wifa,wifa_date)
        VALUES($arhId,$unknownStudentId,'999999999995','Unknown Date Learner','Female','2026-2027','Grade 8',1,'0000-00-00')");
    $unknownBaseline=requestTestGet($base.'health_program_monitoring.php?record_id='.$unknownStudentId,$token);
    assertIntegration(!empty($unknownBaseline['body']['success']) && $unknownBaseline['body']['sf8_baseline']['wifa_date']===null,
        'zero SF8 date is displayed as unknown, not fabricated');
    $baselineDuplicate=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_wifa','record_id'=>$femaleId,
        'event_date'=>'2026-07-15','outcome'=>'Given'],$token);
    assertIntegration($baselineDuplicate['status']===409,'nurse cannot duplicate the imported SF8 starting date');
    $futureWifa=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_wifa','record_id'=>$femaleId,
        'event_date'=>'2099-01-01','outcome'=>'Given'],$token);
    assertIntegration($futureWifa['status']===422,'future dates cannot be recorded as actual administrations');
    $firstWifa=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_wifa','record_id'=>$femaleId,
        'event_date'=>'2026-07-22','outcome'=>'Given'],$token);
    assertIntegration(!empty($firstWifa['body']['success']),'nurse can record an actual WIFA date');
    $duplicateWifa=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_wifa','record_id'=>$femaleId,
        'event_date'=>'2026-07-22','outcome'=>'Given'],$token);
    assertIntegration($duplicateWifa['status']===409,'duplicate WIFA date returns a reviewable conflict');
    $wifaBefore=requestTestGet($base.'health_program_monitoring.php?record_id='.$femaleId,$token);
    $wifaEventId=(int)$wifaBefore['body']['wifa_events'][0]['wifa_event_id'];
    $correctWifa=requestTestApi($base.'health_program_monitoring.php',['action'=>'update_wifa','record_id'=>$femaleId,
        'event_id'=>$wifaEventId,'event_date'=>'2026-07-22'],$token);
    assertIntegration(!empty($correctWifa['body']['success']) && (int)$db->query("SELECT COUNT(*) AS n FROM health_program_event_audit WHERE program='WIFA' AND event_id=$wifaEventId")->fetch_assoc()['n']===2,
        'intake-date correction keeps one event and an audit trail');
    $secondWifa=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_wifa','record_id'=>$femaleId,
        'event_date'=>'2026-07-29','outcome'=>'Given'],$token);
    assertIntegration(!empty($secondWifa['body']['success']),'later WIFA dose is recorded separately');
    $review=requestTestApi($base.'health_program_monitoring.php',['action'=>'save_review','record_id'=>$femaleId,
        'decision'=>'Continue','reason'=>'Nurse reviewed result','hemoglobin_g_dl'=>'12.6','hemoglobin_date'=>'2026-09-20'], $token);
    assertIntegration($review['status']===422,'WIFA review decisions are no longer accepted');
    $deworm=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_deworming','record_id'=>$femaleId,
        'event_date'=>'2026-08-01','channel'=>'SBFP','outcome'=>'Given'],$token);
    assertIntegration(!empty($deworm['body']['success']),'dated deworming event is separate from WIFA');
    $duplicateDeworm=requestTestApi($base.'health_program_monitoring.php',['action'=>'add_deworming','record_id'=>$femaleId,
        'event_date'=>'2026-08-01','channel'=>'SBFP','outcome'=>'Given'],$token);
    assertIntegration($duplicateDeworm['status']===409,'duplicate deworming date is rejected');
    $rawReport=requestTestGet($base.'get_deworming_wifa_raw.php?school_year=2026-2027',$token);
    assertIntegration(!empty($rawReport['body']['success']) && count($rawReport['body']['wifa_events'])===2
        && count($rawReport['body']['deworming_events'])===1,
        'report source includes only actual given events alongside the SF8 baseline');
    $aggregated=requestTestGet($base.'get_table1_deworming_wifa_report.php?school_year=2026-2027',$token);
    $grade8=array_values(array_filter($aggregated['body']['records']??[], static fn($row)=>$row['grade_level']==='8' && $row['sex']==='Female'));
    assertIntegration(!empty($aggregated['body']['success']) && count($grade8)===1
        && (int)$grade8[0]['wifa_jul_sep']===1 && (int)$grade8[0]['sbfp_total']===1,
        'aggregated WIFA and deworming reports count the learner once despite multiple dates');
    $feedingDenied=requestTestGet($base.'feeding_monitoring.php?record_id='.$femaleId,$admin['body']['token']);
    assertIntegration($feedingDenied['status']===403,'school admin cannot read nurse feeding measurements');
    $futureFeeding=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2099-01-01','weight_kg'=>'46'],$token);
    assertIntegration($futureFeeding['status']===422,'future feeding measurement dates are rejected');
    $emptyFeeding=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2026-09-01'],$token);
    assertIntegration($emptyFeeding['status']===422,'feeding follow-up requires weight or BMI');
    $unsupportedRecovery=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2026-09-01','weight_kg'=>'46','progress_status'=>'Recovered'],$token);
    assertIntegration($unsupportedRecovery['status']===422,'recovery assessment requires nurse notes');
    $weightOnly=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2026-09-01','weight_kg'=>'46.00','progress_status'=>'Monitoring'],$token);
    assertIntegration(!empty($weightOnly['body']['success']),'nurse can record weight without inventing BMI');
    $duplicateWeight=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2026-09-01','weight_kg'=>'47'],$token);
    assertIntegration($duplicateWeight['status']===409,'duplicate learner and feeding date returns a conflict');
    $bmiOnly=requestTestApi($base.'feeding_monitoring.php',['action'=>'add','record_id'=>$femaleId,
        'measured_on'=>'2026-09-15','bmi'=>'19.40','progress_status'=>'Improving','notes'=>'Nurse follow-up'],$token);
    $feedingHistory=requestTestGet($base.'feeding_monitoring.php?record_id='.$femaleId,$token);
    assertIntegration(!empty($bmiOnly['body']['success']) && count($feedingHistory['body']['measurements'])===2
        && $feedingHistory['body']['measurements'][0]['bmi']===null
        && $feedingHistory['body']['measurements'][1]['weight_kg']===null,
        'weight and BMI are independent dated follow-up fields');
    $firstMeasurement=(int)$feedingHistory['body']['measurements'][0]['measurement_id'];
    $correction=requestTestApi($base.'feeding_monitoring.php',['action'=>'update','record_id'=>$femaleId,
        'measurement_id'=>$firstMeasurement,'measured_on'=>'2026-09-01','weight_kg'=>'46.50',
        'progress_status'=>'Monitoring','notes'=>'Corrected measurement'],$token);
    $auditCount=(int)$db->query('SELECT COUNT(*) AS n FROM feeding_measurement_audit WHERE measurement_id='.$firstMeasurement)->fetch_assoc()['n'];
    $baselineWeight=$db->query('SELECT weight_kg,bmi FROM sf8_student_records WHERE record_id='.$femaleId)->fetch_assoc();
    assertIntegration(!empty($correction['body']['success']) && $auditCount===2 && (float)$baselineWeight['weight_kg']===45.0,
        'feeding correction is audited and does not overwrite the SF8 baseline');
    $programSnapshot=requestTestGet($base.'get_program_monitoring.php?school_year=2026-2027',$token);
    $summary=$programSnapshot['body']['programs'][$femaleId]??null;
    assertIntegration(!empty($programSnapshot['body']['success']) && $summary
        && $summary['arh']['pregnancy_status']==='Pregnant'
        && count($summary['wifa_events'])===2 && count($summary['deworming_events'])===1
        && count($summary['feeding_measurements'])===2,
        'combined monitoring snapshot links category records and dated follow-ups to one learner');
    echo "End-to-end upload, cloud decryption, preview, and database approval verified using an isolated database.\n";
} finally {
    $cleanupFailure=null;
    foreach($cloudAssets as $cloudAsset){try{sf8RemoveCloudinaryFile($cloudAsset['cloudinary_public_id']);}catch(Throwable $e){$cleanupFailure=$e;}}
    if($db)$db->close();
    foreach([$apiProcess,$dbProcess] as $process){if(is_resource($process)){proc_terminate($process);proc_close($process);}}
    putenv($oldConfig===false?'CLINICDESK_SECURITY_CONFIG':'CLINICDESK_SECURITY_CONFIG='.$oldConfig);
    putenv($oldCa===false?'CURL_CA_BUNDLE':'CURL_CA_BUNDLE='.$oldCa);
    // Delete only the freshly-created, verified test directory (never a user database).
    $resolved=realpath($directory);$tempRoot=realpath(sys_get_temp_dir());
    if($resolved && $tempRoot && str_starts_with(strtolower(str_replace('\\','/',$resolved)),strtolower(str_replace('\\','/',$tempRoot)).'/clinicdesk_sf8_test_')){
        $entries=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
        foreach($entries as $entry){if($entry->isDir())rmdir($entry->getPathname());else unlink($entry->getPathname());}
        rmdir($resolved);
    }
    if($cleanupFailure)throw new RuntimeException('Temporary database removed, but the encrypted cloud test asset could not be deleted.');
}
