#!/usr/bin/env python3
"""
generate_deped_report.py
Fills the supplied DepEd Part IX workbook with ClinicDesk records and saved
school answers for a selected year. The template's layout and formulas are
preserved; DepEd SUM() totals recalculate when opened in Excel.

Usage: python3 generate_deped_report.py <template> <output> <school_year> <data.json>
data.json includes student/program records and saved_reports keyed by section.
The workbook includes record-based Tables 1.A-1.D, Box 1 and Boxes 5-6,
plus saved nurse answers for Boxes 2-4 and 8-11.
"""
import sys, json
import datetime
import openpyxl

def norm_grade(g):
    if g is None: return None
    raw = str(g).strip().lower()
    if "kinder" in raw or raw == "k": return "Kinder"
    if "sned" in raw or "non-graded" in raw or "nongraded" in raw: return "SNEd"
    s = raw.replace("grade", "").strip()
    return "Grade " + s if s.isdigit() else None

def norm_sex(x):
    value = str(x or "").strip().lower()
    if value.startswith("m"): return "M"
    if value.startswith("f"): return "F"
    return None

def truthy(v):
    return str(v).strip().lower() in ("1", "yes", "y", "true", "t")

def put(ws, col, row, val):
    if val is not None and val != "":
        ws[f"{col}{row}"] = val

def choice(value):
    """Return an explicit Yes/No answer; an unanswered question stays blank."""
    text = str(value or "").strip().lower()
    return True if text == "yes" else False if text == "no" else None

def check_mark(selected):
    """Render the paper form's check boxes without Excel TRUE/FALSE text."""
    return "/" if selected else None

def fill_checkboxes(ws, answers, mapping):
    selected = set(answers or [])
    for label, cell in mapping.items():
        ws[cell] = check_mark(label in selected)

def fill_school_boxes(wb, saved):
    """Use saved nurse answers for the template's school-level sections."""
    filled = {}
    if "IX. Box 1" in wb.sheetnames and "box1" in saved:
        fill_checkboxes(wb["IX. Box 1"], saved["box1"].get("referralConcerns"), {
            "Medical, dental and nutritional": "U8",
            "Mental health and well-being": "U9",
            "Adolescent reproductive health": "U10",
            "Drug, substance and tobacco use": "U11",
        })
        filled["box1_school"] = 1
    if "IX. Boxes 2 & 3; Table 2" in wb.sheetnames and "box2_3" in saved:
        ws = wb["IX. Boxes 2 & 3; Table 2"]
        d = saved["box2_3"]
        for field, cell in (("hasSchoolClinic", "AA6"), ("visitedBySDO", "AA8"), ("waterForDrinking", "Z39")):
            ws[cell] = check_mark(choice(d.get(field)))
        if choice(d.get("visitedBySDO")):
            ws["N10"] = int(d.get("sdoVisits") or 0)
        # The supplied template validates SDO visits against a TRUE/FALSE cell.
        # Match the visible slash instead, retaining the original guidance.
        ws["Z10"] = '=IF(AND(clinic_sdo_personnel_yes="/",clinic_sdo_personnel_ifyes=""),"Please specify.",IF(AND(clinic_sdo_personnel_yes<>"/",clinic_sdo_personnel_ifyes<>""),"Please check the \'Yes\' box if you have an entry.",""))'
        equipment = d.get("clinicEquipment") or {}
        for row, label in enumerate(("Bathroom", "Hospital/Clinic Bed", "Dental Chair", "First Aid Kit", "Height Tool", "Weighing Scale", "Autoclave/Sterilizer", "BP Apparatus", "Nebulizer"), 15):
            status = str(equipment.get(label) or "").lower()
            if status:
                ws[f"U{row}"] = check_mark(status == "functional")
                ws[f"Z{row}"] = check_mark(status in ("non-functional", "non functional"))
        fill_checkboxes(ws, d.get("waterSources"), {"Piped water": "Z34", "Water Well": "Z35", "Rainwater Catchment": "Z36", "Natural Source": "Z37"})
        filled["box2_3"] = 1
    if "IX. Boxes 2 & 3; Table 2" in wb.sheetnames and "box4" in saved:
        ws = wb["IX. Boxes 2 & 3; Table 2"]
        cases = saved["box4"].get("mentalHealthCases") or {}
        for case_type, row in (("deathInside", 56), ("deathOutside", 58), ("attemptInside", 60), ("attemptOutside", 62)):
            values = cases.get(case_type) or {}
            for group, col in (("elemLearners", "N"), ("elemPersonnel", "S"), ("jhsLearners", "X"), ("jhsPersonnel", "AC"), ("shsLearners", "AH"), ("shsPersonnel", "AM")):
                put(ws, col, row, values.get(group))
        filled["table2"] = 1
    if "IX. Box 4" in wb.sheetnames and "box4" in saved:
        ws = wb["IX. Box 4"]
        d = saved["box4"]
        for field, cell in (("hasGuidanceOffice", "U6"), ("hasMentalHealthTraining", "AJ34")):
            answer = choice(d.get(field))
            ws[cell] = check_mark(answer)
        for level, row in (("JHS", 14), ("SHS", 16)):
            counts = d.get("counseling" + level) or {}
            for field, col in (("male", "M"), ("female", "S")):
                put(ws, col, row, counts.get(field))
        for level, row in (("JHS", 27), ("SHS", 29)):
            counts = d.get("vulnerable" + level) or {}
            for field, col in (("muslim", "M"), ("ip", "S"), ("lwd", "Y")):
                put(ws, col, row, counts.get(field))
        topics = d.get("mentalHealthTraining") or {}
        for field, col in (("bullying", "M"), ("mentalHealth", "S"), ("suicidePrevention", "Y"), ("selfCare", "AE"), ("psychologicalFirstAid", "AK"), ("crisisResponse", "AQ")):
            put(ws, col, 46, topics.get(field))
        filled["box4"] = 1
    if "IX. Boxes 5 & 6" in wb.sheetnames and "box5_6" in saved:
        ws = wb["IX. Boxes 5 & 6"]
        d = saved["box5_6"]
        answer = choice(d.get("hasSupportCenter"))
        ws["V13"] = check_mark(answer)
        fill_checkboxes(ws, d.get("iecMaterials"), {"No Smoking Signages": "AS27", "Poster prohibiting cigarette sales": "AS28"})
        fill_checkboxes(ws, d.get("storesSelling"), {"Tobacco products": "AS31", "Vape/e-cigarettes": "AS32"})
        intervention = d.get("tobaccoIntervention") or {}
        for level, col in (("jhs", "Z"), ("shs", "AG")):
            values = intervention.get(level) or {}
            put(ws, col, 49, values.get("users"))
            put(ws, col, 51, values.get("bti"))
        filled["box5_6_school"] = 1
    if "IX. Boxes 7 to 9" in wb.sheetnames and "box8_9" in saved:
        ws = wb["IX. Boxes 7 to 9"]
        d = saved["box8_9"]
        answer = choice(d.get("drugEducation"))
        ws["Z6"] = check_mark(answer)
        fill_checkboxes(ws, d.get("drugComponents"), {
            "Curriculum integration": "Z9", "Extra-curricular activities": "Z10",
            "Barangay Anti-Drug Abuse Council partnership": "Z11"})
        for grade, col in ((7,"D"),(8,"G"),(9,"J"),(10,"M"),(11,"S"),(12,"V")):
            put(ws, col, 17, (d.get("drugLifeSkills") or {}).get(f"g{grade}"))
        for field, cell in (("sanitaryPermit", "W32"), ("healthCertificates", "W34"), ("hasKitchen", "W36")):
            answer = choice(d.get(field))
            ws[cell] = check_mark(answer)
        manager = str(d.get("canteenManager") or "")
        for label, cell in (("School", "W28"), ("Teacher-Coop", "W29")):
            ws[cell] = check_mark(manager == label)
        if manager == "Others": ws["M30"] = d.get("canteenManagerOther") or "Others"
        fill_checkboxes(ws, d.get("feedingFundSources"), {"School MOOE": "T46", "School Canteen Fund": "T47", "LGU Fund": "T48", "PTA Fund": "T49", "Barangay Fund": "T50", "Private Individual/Sector Fund": "T51", "SBFP": "T52"})
        fill_checkboxes(ws, d.get("agriResources"), {"Gulayan sa Paaralan": "T55", "Fish Pond": "T56", "Agricultural Crops": "T57", "Livestock": "T58"})
        filled["box8_9"] = 1
    if "IX. Boxes 10 & 11" in wb.sheetnames and "box10_11" in saved:
        ws = wb["IX. Boxes 10 & 11"]
        d = saved["box10_11"]
        fill_checkboxes(ws, d.get("swmImplementation"), dict(zip(("Composting", "Trash collection point", "Poster/Slogan contest", "Posting signage", "Recycling projects", "Barangay SWM representative", "Use of paper plates/cups", "Use of recycled materials as teaching tools", "Use of reusable food containers", "Waste segregation"), (f"AB{row}" for row in range(8, 18)))))
        fill_checkboxes(ws, d.get("stakeholders"), dict(zip(("Barangay", "Community leaders", "Local business partners", "Municipal/City government", "Parents"), (f"AB{row}" for row in range(20, 25)))))
        fill_checkboxes(ws, d.get("sanitaryPadLocations"), {"School Canteen": "L34", "School Clinic": "L35", "Guidance Office": "L36"})
        if "Others" in (d.get("sanitaryPadLocations") or []): ws["L37"] = d.get("sanitaryPadOther") or "Others"
        filled["box10_11"] = 1
    return filled

# ---- Table 1.B Nutrition ----
NUTRI_SHEET = "IX. Table 1.A & 1.B"
ELEM_ROWS = {"Normal":22,"Obese":23,"Overweight":24,"Severely Wasted":25,"Wasted":26}
ELEM_COLS = {"Kinder":("I","L"),"Grade 1":("O","R"),"Grade 2":("U","X"),"Grade 3":("AA","AD"),
             "Grade 4":("AG","AJ"),"Grade 5":("AM","AP"),"Grade 6":("AS","AV"),"SNEd":("AY","BB")}
SEC_ROWS = {"Normal":33,"Obese":34,"Overweight":35,"Severely Wasted":36,"Wasted":37}
JHS_COLS = {"Grade 7":("I","L"),"Grade 8":("O","R"),"Grade 9":("U","X"),"Grade 10":("AA","AD")}
SHS_COLS = {"Grade 11":("AM","AP"),"Grade 12":("AS","AV")}

def norm_bmi(c):
    s = str(c or "").strip().lower()
    if "severely" in s and "wast" in s: return "Severely Wasted"
    if "wast" in s: return "Wasted"
    if "obese" in s: return "Obese"
    if "overweight" in s: return "Overweight"
    if "normal" in s: return "Normal"
    return None

def fill_nutrition(ws, students, sy):
    counts = {}
    for r in students:
        if str(r.get("school_year","")).strip() != sy: continue
        grade = norm_grade(r.get("grade_level")); cat = norm_bmi(r.get("bmi_category"))
        if not grade or not cat: continue
        sex = norm_sex(r.get("sex"))
        if not sex: continue
        counts[(grade,sex,cat)] = counts.get((grade,sex,cat),0)+1
    total = 0
    for (grade,sex,cat),n in counts.items():
        if grade in ELEM_COLS: rows,cols = ELEM_ROWS,ELEM_COLS
        elif grade in JHS_COLS: rows,cols = SEC_ROWS,JHS_COLS
        elif grade in SHS_COLS: rows,cols = SEC_ROWS,SHS_COLS
        else: continue
        if cat not in rows: continue
        mcol,fcol = cols[grade]
        put(ws, mcol if sex=="M" else fcol, rows[cat], n); total += n
    return total

# ---- Table 1.A Immunization (TOTAL learner columns) ----
IMMUN_ROWS = {"Measles Rubella":11,"Tetanus Diphtheria":12,"Human Papilloma Virus":13}
IMMUN_COLS = {"Grade 1":("L","Q"),"Grade 4":("AD","AD"),"Grade 7":("AQ","AV")}

def norm_vaccine(v):
    s = str(v or "").strip().lower()
    if "measles" in s or s == "mr" or "rubella" in s: return "Measles Rubella"
    if "tetanus" in s or s == "td" or "diphther" in s: return "Tetanus Diphtheria"
    if "hpv" in s or "papilloma" in s: return "Human Papilloma Virus"
    return None

def fill_immunization(ws, immun, sy):
    counts = {}
    seen = set()
    for index, r in enumerate(immun):
        if not truthy(r.get("immunized",1)): continue
        vac = norm_vaccine(r.get("vaccine")); grade = norm_grade(r.get("grade_level"))
        if not vac or not grade: continue
        sex = norm_sex(r.get("sex"))
        if not sex: continue
        learner = str(r.get("lrn") or "").strip() or ("unknown-row", index)
        recipient = (learner, vac, grade, sex)
        if recipient in seen: continue
        seen.add(recipient)
        counts[(vac,grade,sex)] = counts.get((vac,grade,sex),0)+1
    total = 0
    for (vac,grade,sex),n in counts.items():
        row = IMMUN_ROWS.get(vac); cols = IMMUN_COLS.get(grade)
        if not row or not cols: continue
        mcol,fcol = cols
        put(ws, mcol if sex=="M" else fcol, row, n); total += n
    return total

# ---- Table 1.C Deworming ----
DEWORM_SHEET = "IX. Table 1.C & 1.D"
DEWORM_ROWS = {"Kinder":11,"Grade 1":12,"Grade 2":13,"Grade 3":14,"Grade 4":15,"Grade 5":16,
               "Grade 6":17,"SNEd":18,"Grade 7":21,"Grade 8":22,"Grade 9":23,"Grade 10":24,
               "Grade 11":27,"Grade 12":28}

def fill_deworming(ws, deworm, sy):
    sbfp = {}; other = {}; seen_sbfp = set(); seen_other = set()
    for index, r in enumerate(deworm):
        grade = norm_grade(r.get("grade_level"))
        if not grade: continue
        sex = norm_sex(r.get("sex"))
        if not sex: continue
        lrn = str(r.get("lrn") or "").strip()
        learner = (lrn, str(r.get("school_year") or "").strip()) if lrn else ("unknown-row", index)
        recipient = (learner, grade, sex)
        if truthy(r.get("dewormed_sbfp")) and recipient not in seen_sbfp:
            seen_sbfp.add(recipient); sbfp[(grade,sex)] = sbfp.get((grade,sex),0)+1
        if truthy(r.get("dewormed_other")) and recipient not in seen_other:
            seen_other.add(recipient); other[(grade,sex)] = other.get((grade,sex),0)+1
    total = 0
    for (grade,sex),n in sbfp.items():
        row = DEWORM_ROWS.get(grade)
        if row: put(ws, "L" if sex=="M" else "Q", row, n); total += n
    for (grade,sex),n in other.items():
        row = DEWORM_ROWS.get(grade)
        if row: put(ws, "AA" if sex=="M" else "AF", row, n); total += n
    return total

# ---- Table 1.D WIFA (new) ----
def fill_wifa(ws, deworm, sy):
    # Count the recorded start and last given dose in the two report windows.
    # Multiple administrations in the same window count one learner once.
    wifa_counts = {}  # (grade, period) -> distinct learner count
    try:
        first_year, last_year = (int(part) for part in sy.split("-", 1))
    except (ValueError, AttributeError):
        return 0
    ws["C35"] = f"Number of female learners given WIFA supplements from July to September {first_year}"
    ws["C37"] = f"Number of female learners given WIFA supplements from January to March {last_year}"
    seen = set()
    for index, r in enumerate(deworm):
        # Skip if not WIFA or not female
        if not truthy(r.get("wifa")):
            continue
        sex = norm_sex(r.get("sex"))
        if sex != "F":
            continue
        grade = norm_grade(r.get("grade_level"))
        if not grade:
            continue
        # Determine period from wifa_date
        date_str = str(r.get("wifa_date", "")).strip()
        if not date_str:
            continue
        try:
            dt = datetime.datetime.strptime(date_str, "%Y-%m-%d")
            if dt.year == first_year and 7 <= dt.month <= 9:
                period = "jul_sep"
            elif dt.year == last_year and 1 <= dt.month <= 3:
                period = "jan_mar"
            else:
                continue
        except ValueError:
            continue
        lrn = str(r.get("lrn") or "").strip()
        learner = (lrn, str(r.get("school_year") or "").strip()) if lrn else ("unknown-row", index)
        recipient = (learner, grade, period)
        if recipient in seen: continue
        seen.add(recipient)
        key = (grade, period)
        wifa_counts[key] = wifa_counts.get(key, 0) + 1

    # Each grade's output occupies a merged block; use its top-left cell.
    grade_cols = {
        "Grade 7": "Q",
        "Grade 8": "V",
        "Grade 9": "AA",
        "Grade 10": "AF",
        "Grade 11": "AP",
        "Grade 12": "AU",
    }

    # Both JHS and SHS grade blocks span rows 35-36 and 37-38.
    rows = {"jul_sep": 35, "jan_mar": 37}

    total = 0
    for (grade, period), count in wifa_counts.items():
        col = grade_cols.get(grade)
        if not col: continue
        row = rows.get(period)
        if row:
            put(ws, col, row, count)
            total += count
    return total

# ---- Box 1 (OKD & LHAS) ----
LHAS_SHEET = "IX. Box 1"
# Component -> row within each section (top-left of the merged data cell).
LHAS_COMP_ROW = {
    "ELEM": {"nutritional assessment":21,"health history":22,"vision screening":24,
             "hearing screening":25,"oral health":26,"cars":27,"rapid heeadsss":29},
    "JHS":  {"nutritional assessment":35,"health history":36,"vision screening":38,
             "hearing screening":39,"oral health":40,"cars":41,"rapid heeadsss":43},
    "SHS":  {"nutritional assessment":49,"health history":50,"vision screening":52,
             "hearing screening":53,"oral health":54,"cars":55,"rapid heeadsss":57},
}
# Column letters for each metric.
LHAS_COLS = {"masterlisted":"P","screened":"T","findings":"X",
             "referred_school":"AB","referred_lgu":"AF","referred_private":"AJ","referred_others":"AN"}

def norm_component(c):
    s = str(c or "").strip().lower()
    if "nutrition" in s: return "nutritional assessment"
    if "health history" in s or "hhi" in s: return "health history"
    if "vision" in s: return "vision screening"
    if "hearing" in s: return "hearing screening"
    if "oral" in s: return "oral health"
    if "cars" in s or "adolescent risk" in s: return "cars"
    if "heeadsss" in s or "heeadss" in s: return "rapid heeadsss"
    return None

def level_from_grade(grade):
    g = norm_grade(grade)
    if g in ("Kinder","Grade 1","Grade 2","Grade 3","Grade 4","Grade 5","Grade 6","SNEd"):
        return "ELEM"
    if g in ("Grade 7","Grade 8","Grade 9","Grade 10"):
        return "JHS"
    if g in ("Grade 11","Grade 12"):
        return "SHS"
    return None

def fill_lhas(ws, lhas, sy):
    # Aggregate per (level, component): sum each metric.
    agg = {}
    for r in lhas:
        comp = norm_component(r.get("screening_type"))
        level = level_from_grade(r.get("grade_level"))
        if not comp or not level:
            continue
        key = (level, comp)
        if key not in agg:
            agg[key] = {m: 0 for m in LHAS_COLS}
        agg[key]["masterlisted"]     += int(r.get("masterlisted", 0) or 0)
        agg[key]["screened"]         += int(r.get("screened", 0) or 0)
        agg[key]["findings"]         += int(r.get("findings", 0) or 0)
        agg[key]["referred_school"]  += int(r.get("referred_school", 0) or 0)
        agg[key]["referred_lgu"]     += int(r.get("referred_lgu", 0) or 0)
        agg[key]["referred_private"] += int(r.get("referred_private", 0) or 0)
        agg[key]["referred_others"]  += int(r.get("referred_others", 0) or 0)

    total = 0
    for (level, comp), metrics in agg.items():
        row = LHAS_COMP_ROW.get(level, {}).get(comp)
        if not row:
            continue
        for metric, col in LHAS_COLS.items():
            put(ws, col, row, metrics[metric])
            total += metrics[metric]
    return total

# ---- Boxes 5 & 6 (ARH + Tobacco) ----
BOX56_SHEET = "IX. Boxes 5 & 6"
# Pregnant learners: grade -> column (single count column per grade, merged).
ARH_GRADE_COLS = {
    "Grade 4": "P", "Grade 5": "S", "Grade 6": "V",
    "Grade 7": "AB", "Grade 8": "AE", "Grade 9": "AH", "Grade 10": "AK",
    "Grade 11": "AQ", "Grade 12": "AT",
}
ARH_ROWS = {"in school": 10, "adm": 11}  # In School = row 10, ADM = row 11

def fill_box5_box6(ws, arh_list, peer_educators, tobacco_list):
    filled = 0
    # --- Box 5: pregnant learners by grade + delivery mode ---
    counts = {}  # (grade, mode_row) -> total
    for r in arh_list:
        grade = norm_grade(r.get("grade_level"))
        if grade not in ARH_GRADE_COLS:
            continue
        mode = str(r.get("delivery_mode", "")).strip().lower()
        if "adm" in mode or "alternative" in mode:
            row = ARH_ROWS["adm"]
        else:
            row = ARH_ROWS["in school"]  # blank/in-school both go here
        total = int(r.get("total", 1) or 1)
        counts[(grade, row)] = counts.get((grade, row), 0) + total

    for (grade, row), n in counts.items():
        put(ws, ARH_GRADE_COLS[grade], row, n)
        filled += n

    # Peer educators -> AK15
    if peer_educators:
        put(ws, "AK", 15, int(peer_educators))

    # --- Box 6: tobacco brought / referred by level ---
    # Brought tobacco table: row 39 (brought), row 41 (referred).
    # Columns: Elementary=S, JHS=Z, SHS=AG.
    lvl_col = {"elem": "S", "jhs": "Z", "shs": "AG"}
    for t in tobacco_list:
        grp = t.get("level_group", "jhs")
        col = lvl_col.get(grp)
        if not col:
            continue
        put(ws, col, 39, int(t.get("brought", 0)))
        put(ws, col, 41, int(t.get("referred", 0)))
        filled += int(t.get("brought", 0))
    return filled

def main():
    if len(sys.argv) < 5:
        print(json.dumps({"success":False,"message":"usage: template output school_year data.json"})); sys.exit(1)
    template, output, sy, data_path = sys.argv[1:5]
    with open(data_path, encoding="utf-8") as f:
        data = json.load(f)
    students = data.get("students", []); immun = data.get("immunization", []); deworm = data.get("deworming", [])
    arh = data.get("arh", []); peer_educators = data.get("peer_educators", 0); tobacco = data.get("tobacco", [])
    lhas = data.get("lhas", [])
    wb = openpyxl.load_workbook(template)
    school = data.get("school") or {}
    if school.get("name") and school.get("id") and "Part IX. School Health" in wb.sheetnames:
        wb["Part IX. School Health"]["D5"] = f"{school['name']} - {school['id']}"
    # The supplied workbook is a reusable school-year format, not a fixed-year report.
    for ws in wb.worksheets:
        for row in ws.iter_rows(min_row=1, max_row=min(ws.max_row, 55)):
            for cell in row:
                if cell.data_type == "s" and isinstance(cell.value, str) and "SY 2025" in cell.value:
                    cell.value = cell.value.replace("SY 2025–2026", "SY " + sy).replace("SY 2025-2026", "SY " + sy).replace("SY 2025\ufffd2026", "SY " + sy)
                    cell.value = cell.value.replace("March 31, 2026", "March 31, " + sy.split("-")[1])
    filled = {"nutrition":0,"immunization":0,"deworming":0,"box5_6":0,"lhas":0, "wifa":0}
    filled.update(fill_school_boxes(wb, data.get("saved_reports") or {}))
    if NUTRI_SHEET in wb.sheetnames:
        filled["nutrition"] = fill_nutrition(wb[NUTRI_SHEET], students, sy)
        filled["immunization"] = fill_immunization(wb[NUTRI_SHEET], immun, sy)
    if DEWORM_SHEET in wb.sheetnames:
        filled["deworming"] = fill_deworming(wb[DEWORM_SHEET], deworm, sy)
        filled["wifa"] = fill_wifa(wb[DEWORM_SHEET], deworm, sy)   # <-- NEW
    if BOX56_SHEET in wb.sheetnames:
        filled["box5_6"] = fill_box5_box6(wb[BOX56_SHEET], arh, peer_educators, tobacco)
    if LHAS_SHEET in wb.sheetnames:
        filled["lhas"] = fill_lhas(wb[LHAS_SHEET], lhas, sy)
    # The official Part IX template contains FALSE placeholders in answer boxes.
    # Normalize those as well so untouched boxes never display TRUE/FALSE in Excel.
    for ws in wb.worksheets:
        if ws.title.startswith("IX."):
            for row in ws:
                for cell in row:
                    if isinstance(cell.value, bool):
                        cell.value = check_mark(cell.value)
    wb.save(output)
    print(json.dumps({"success":True,"message":f"Report generated for {sy}.","filled":filled,"output":output}))

if __name__ == "__main__":
    main()
