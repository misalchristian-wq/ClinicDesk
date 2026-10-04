"""Contract checks for the 18-input symptom CSV model."""
import sys
import unittest
from unittest.mock import patch
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "ml_model"))
import app as model_app

class PredictionContractTest(unittest.TestCase):
    def setUp(self):
        self.payload = {
            "Age": 16, "Gender": "Female", "Diet Type": "Vegetarian",
            "Living Environment": "Rural", "Skin Condition": "Normal",
            "Night Blindness": 0, "Dry Eyes": 0, "Bleeding Gums": 0,
            "Fatigue": 1, "Tingling Sensation": 0, "Low Sun Exposure": 1,
            "Reduced Memory Capacity": 0, "Shortness of Breath": 0,
            "Loss of Appetite": 0, "Fast Heart Rate": 0, "Brittle Nails": 0,
            "Weight Loss": 0, "Reduced Wound Healing Capacity": 0,
        }

    def test_all_csv_predictors_required(self):
        self.assertEqual(set(self.payload), set(model_app.FEATURE_COLS))
        self.assertEqual(model_app.build_feature_vector(self.payload)[0]["Living Environment"], "Rural")
        for key in model_app.FEATURE_COLS:
            incomplete = self.payload.copy()
            del incomplete[key]
            with self.assertRaisesRegex(ValueError, "Missing model inputs"):
                model_app.build_feature_vector(incomplete)

    def test_invalid_values_rejected(self):
        for key, value in (("Age", 4), ("Skin Condition", "Other"),
                           ("Dry Eyes", "Yes"), ("Diet Type", "Balanced")):
            invalid = self.payload.copy()
            invalid[key] = value
            with self.assertRaises(ValueError):
                model_app.build_feature_vector(invalid)

    def test_endpoint_predicts_without_labs(self):
        response = model_app.app.test_client().post("/predict", json=self.payload)
        self.assertEqual(response.status_code, 200)
        result = response.get_json()
        self.assertTrue(result["success"])
        self.assertIn(result["predicted_deficiency"], model_app.meta["deficiency_classes"])

    def test_iron_supplement_is_conditional(self):
        with patch.object(model_app.model, 'predict', return_value=['Iron']):
            result = model_app.app.test_client().post("/predict", json=self.payload).get_json()
        self.assertTrue(result["success"])
        self.assertIn("ferrous sulfate", result["recommendation_text"].lower())
        self.assertIn("authorized", result["recommendation_text"].lower())

if __name__ == "__main__":
    unittest.main()
