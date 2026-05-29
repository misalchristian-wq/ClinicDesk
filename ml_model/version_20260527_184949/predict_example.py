
import joblib
import pickle
import json
import pandas as pd
import numpy as np

class ClinicDeskPredictor:
    def __init__(self, model_dir):
        self.model = joblib.load(f"{model_dir}/Random_Forest_model.joblib")
        self.scaler = joblib.load(f"{model_dir}/scaler.joblib")
        with open(f"{model_dir}/target_encoder.pkl", 'rb') as f:
            self.target_encoder = pickle.load(f)
        with open(f"{model_dir}/feature_columns.json", 'r') as f:
            self.feature_columns = json.load(f)['feature_columns']
    
    def predict(self, input_data):
        df = pd.DataFrame([input_data])
        for col in self.feature_columns:
            if col not in df.columns:
                df[col] = 0
        df = df[self.feature_columns].fillna(0)
        scaled = self.scaler.transform(df)
        pred_encoded = self.model.predict(scaled)[0]
        return self.target_encoder.inverse_transform([pred_encoded])[0]

# Example usage
if __name__ == "__main__":
    predictor = ClinicDeskPredictor(".")
    sample = {"age": 15, "gender": "Female", "bmi": 18.5}
    print(predictor.predict(sample))
