<?php
// api/record_categories.php
function clinicRecordCategories() {
    return [
        "nutrition" => [
            "label" => "Nutrition (SF8)",
            "table" => "sf8_student_records",
            "pk"    => "record_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",            "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "section"        => ["label" => "Section",        "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",             "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "age"            => ["label" => "Age",             "type" => "int",   "edit" => true],
                "weight_kg"      => ["label" => "Weight (kg)",     "type" => "float", "edit" => true],
                "height_m"       => ["label" => "Height (m)",      "type" => "float", "edit" => true],
                // Computed fields – not shown as editable inputs, but must be listed here
                // so the UPDATE loop in update_category_records.php can include them in the SET clause.
                "bmi"            => ["label" => "BMI",             "type" => "float", "edit" => false],
                "height_squared" => ["label" => "Height²",         "type" => "float", "edit" => false],
                "bmi_category"   => ["label" => "BMI Category",    "type" => "text",  "edit" => false],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
                // school_year is needed but not shown – we'll auto-fill
            ],
        ],
        "lhas" => [
            "label" => "OKD & LHAS",
            "table" => "okd_lhas_records",
            "pk"    => "okd_lhas_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",             "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",            "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "screening_type" => ["label" => "Screening",      "type" => "text", "edit" => true],
                "masterlisted"   => ["label" => "Masterlisted",   "type" => "int",   "edit" => true],
                "screened"       => ["label" => "Screened",       "type" => "int",   "edit" => true],
                "findings"       => ["label" => "Findings",       "type" => "int",   "edit" => true],
                "referred_school" => ["label" => "Ref. School",   "type" => "int",   "edit" => true],
                "referred_lgu"   => ["label" => "Ref. LGU",       "type" => "int",   "edit" => true],
                "referred_private" => ["label" => "Ref. Private", "type" => "int",   "edit" => true],
                "referred_others" => ["label" => "Ref. Others",   "type" => "int",   "edit" => true],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
            ],
        ],
        "deworming" => [
            "label" => "Deworming & WIFA",
            "table" => "deworming_wifa_records",
            "pk"    => "deworming_wifa_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",             "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",            "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "dewormed_sbfp"  => ["label" => "Dewormed SBFP",  "type" => "bool", "edit" => true],
                "dewormed_other" => ["label" => "Dewormed Other", "type" => "bool", "edit" => true],
                "wifa"           => ["label" => "WIFA",           "type" => "bool", "edit" => true],
                "wifa_date"      => ["label" => "WIFA Date",      "type" => "date", "edit" => true],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
            ],
        ],
        "immunization" => [
            "label" => "Immunization",
            "table" => "immunization_records",
            "pk"    => "immunization_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",             "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",            "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "vaccine"        => ["label" => "Vaccine",        "type" => "text", "edit" => true],
                "dose"           => ["label" => "Dose",           "type" => "text", "edit" => true],
                "immunized"      => ["label" => "Immunized",      "type" => "bool", "edit" => true],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
            ],
        ],
        "arh" => [
            "label" => "Adolescent Reproductive Health",
            "table" => "arh_records",
            "pk"    => "arh_record_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",             "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",            "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "pregnancy_status" => ["label" => "Pregnancy",    "type" => "enum", "edit" => true, "options" => ["Pregnant","Not Pregnant"]],
                "delivery_mode"  => ["label" => "Delivery Mode",  "type" => "enum", "edit" => true, "options" => ["","In School","ADM"]],
                "peer_educator"  => ["label" => "Peer Educator",  "type" => "bool", "edit" => true],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
            ],
        ],
        "tobacco" => [
            "label" => "Tobacco Control",
            "table" => "tobacco_control_records",
            "pk"    => "tobacco_id",
            "fields" => [
                "lrn"            => ["label" => "LRN",             "type" => "text", "edit" => true],
                "learner_name"   => ["label" => "Learner",        "type" => "text", "edit" => true],
                "grade_level"    => ["label" => "Grade",          "type" => "text", "edit" => true],
                "sex"            => ["label" => "Sex",            "type" => "enum",  "edit" => true, "options" => ["Male","Female"]],
                "violation_type" => ["label" => "Violation Type", "type" => "text", "edit" => true],
                "referred_to_care" => ["label" => "Referred",     "type" => "bool", "edit" => true],
                "remarks"        => ["label" => "Remarks",        "type" => "text", "edit" => true],
            ],
        ],
    ];
}