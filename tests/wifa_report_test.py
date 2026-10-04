"""Distinct-recipient checks for the downloadable DepEd workbook."""
import importlib.util
from pathlib import Path

from openpyxl import Workbook

source = Path(__file__).resolve().parents[1] / "report_generator" / "generate_deped_report.py"
spec = importlib.util.spec_from_file_location("deped_generator", source)
generator = importlib.util.module_from_spec(spec)
spec.loader.exec_module(generator)

rows = [
    {"lrn": "111", "school_year": "2026-2027", "grade_level": "Grade 8", "sex": "Female",
     "dewormed_sbfp": 1, "dewormed_other": 0, "wifa": 1, "wifa_date": "2026-07-15"},
    {"lrn": "111", "school_year": "2026-2027", "grade_level": "Grade 8", "sex": "Female",
     "dewormed_sbfp": 0, "dewormed_other": 0, "wifa": 1, "wifa_date": "2026-07-22"},
    {"lrn": "111", "school_year": "2026-2027", "grade_level": "Grade 8", "sex": "Female",
     "dewormed_sbfp": 1, "dewormed_other": 0, "wifa": 1, "wifa_date": "2026-07-29"},
    {"lrn": "222", "school_year": "2026-2027", "grade_level": "Grade 8", "sex": "Female",
     "dewormed_sbfp": 0, "dewormed_other": 1, "wifa": 1, "wifa_date": None},
]
sheet = Workbook().active
assert generator.fill_wifa(sheet, rows, "2026-2027") == 1
assert sheet["U35"].value == 1, "Repeated WIFA dates must count one female learner per period"
assert generator.fill_deworming(sheet, rows, "2026-2027") == 2
assert sheet["Q22"].value == 1, "SF8 and nurse deworming entries must count once"
assert sheet["AF22"].value == 1
print("DepEd WIFA and deworming distinct-recipient tests passed")
