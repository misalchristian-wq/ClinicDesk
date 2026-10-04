# Symptom-model feature audit

ClinicDesk uses the 19-column `symptom_based_vitamin_deficiency_dataset_final.csv` from [Nutrient Deficiency Based on Symptoms](https://www.kaggle.com/datasets/ojasiam/nutrient-deficiency-based-on-symptoms). The CSV is in `ml_model/data/`; SHA-256 is `fa2a6e94df7f66daea748085cb651cd54d4451913791fef1c2ea32ef7a61553c`. Its 18 predictors are all entered or sourced from the learner record. The nineteenth column, **Predicted Deficiency**, is the outcome used during training and never an input. Feeding it to inference would leak the answer.

| CSV input | ClinicDesk source |
| --- | --- |
| Age, Gender | Student record age and sex |
| Diet Type | Vegetarian / Non-Vegetarian assessment choice |
| Living Environment | Rural / Urban assessment choice |
| Skin Condition | Normal / Dry Skin / Rough Skin / Pale/Yellow Skin assessment choice |
| Low Sun Exposure | 1 when the assessment says Low, otherwise 0 |
| Night Blindness, Dry Eyes, Bleeding Gums, Fatigue, Tingling Sensation, Reduced Memory Capacity, Shortness of Breath, Loss of Appetite, Fast Heart Rate, Brittle Nails, Weight Loss, Reduced Wound Healing Capacity | Explicit assessment checkboxes |

The reproducible script is `ml_model/train_symptom_model.py`. It uses a stratified 75/25 split with seed 42 and a random forest pipeline. The 250-row source holdout had 32.8% accuracy and 0.1901 macro F1. Only 37 holdout rows were ages 5–19; in that subgroup accuracy was 21.62% and macro F1 was 0.1033. The CSV calls its target **Predicted Deficiency**; its clinical ground-truth provenance is not documented here. These values are poor, and there is no independent validation on Philippine learners. The model output is a **screening prompt for nurse review**, not evidence of a deficiency, a diagnosis, or a basis for automatic medication. A new local validation dataset and clinical review are needed before treating it as a reliable school-health decision tool.

The previous artifact in `version_20260709_152358` remains archived for provenance. Its four laboratory inputs and unrelated features are no longer used by the current prediction endpoint. Existing laboratory values remain in the database for historical records; the current form does not ask for them.

The nurse starts the local service from Prediction Settings with her account password. Install the runtime once with `py -3 -m venv .venv-ml` and `.venv-ml/Scripts/python.exe -m pip install -r ml_model/requirements.txt`. The service listens on `127.0.0.1:5001`.
