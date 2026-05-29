from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pickle
import json
import pandas as pd
import numpy as np
import os

app = Flask(__name__)
CORS(app)

# ============================================
# UPDATE THIS PATH TO YOUR EXTRACTED MODEL FOLDER
MODEL_DIR = "/Applications/XAMPP/xamppfiles/htdocs/clinicdesk/ml_model/version_20260527_184949"
# ============================================

model = joblib.load(os.path.join(MODEL_DIR, "Decision_Tree_model.joblib"))
scaler = joblib.load(os.path.join(MODEL_DIR, "scaler.joblib"))

with open(os.path.join(MODEL_DIR, "target_encoder.pkl"), 'rb') as f:
    target_encoder = pickle.load(f)

with open(os.path.join(MODEL_DIR, "feature_columns.json"), 'r') as f:
    feature_columns = json.load(f)['feature_columns']

print(f"Model loaded. Expecting {len(feature_columns)} features.")

# Simple mapping for recommendations (you can expand later)
deficiency_recommendations = {
    "Vitamin A deficiency": {
        "foods": "Carrots, sweet potatoes, spinach, eggs, dairy",
        "description": "Increase vitamin A intake through colorful vegetables and fruits.",
        "intervention": "Dietary supplementation if needed"
    },
    "Vitamin C deficiency": {
        "foods": "Citrus fruits, bell peppers, strawberries, broccoli",
        "description": "Eat fresh fruits and vegetables daily.",
        "intervention": "Encourage intake of vitamin C rich foods"
    },
    "Vitamin D deficiency": {
        "foods": "Fatty fish, egg yolks, fortified milk, sunlight exposure",
        "description": "Safe sun exposure and vitamin D rich foods.",
        "intervention": "Sunlight and supplementation"
    },
    "Iron deficiency (Anemia)": {
        "foods": "Lean red meat, spinach, beans, fortified cereals",
        "description": "Combine iron-rich foods with vitamin C for better absorption.",
        "intervention": "Iron supplementation and dietary changes"
    }
}

def get_recommendation(deficiency):
    for key in deficiency_recommendations:
        if key.lower() in deficiency.lower():
            return deficiency_recommendations[key]
    return {
        "foods": "Maintain a balanced diet with fruits, vegetables, whole grains, and lean proteins",
        "description": "No specific deficiency detected. Continue healthy eating habits.",
        "intervention": "Routine monitoring"
    }

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "message": "No input data"}), 400

        # Convert to DataFrame
        input_df = pd.DataFrame([data])
        # Ensure all features exist
        for col in feature_columns:
            if col not in input_df.columns:
                input_df[col] = 0
        input_df = input_df[feature_columns].fillna(0)

        # Scale
        input_scaled = scaler.transform(input_df)

        # Predict
        pred_encoded = model.predict(input_scaled)[0]
        deficiency = target_encoder.inverse_transform([pred_encoded])[0]

        # Confidence (if model supports predict_proba)
        if hasattr(model, "predict_proba"):
            proba = model.predict_proba(input_scaled)[0]
            confidence = float(np.max(proba))
        else:
            confidence = 0.85

        # Simple risk heuristic
        if "severe" in deficiency.lower() or "anemia" in deficiency.lower():
            risk = "High"
        elif "deficiency" in deficiency.lower():
            risk = "Moderate"
        else:
            risk = "Low"

        rec = get_recommendation(deficiency)

        return jsonify({
            "success": True,
            "predicted_deficiency": deficiency,
            "predicted_risk_level": risk,
            "confidence_score": confidence,
            "algorithm_used": "Decision Tree",   # Changed from "Random Forest"
            "recommendation_text": rec["description"],
            "recommended_foods": rec["foods"],
            "intervention_type": rec["intervention"]
        })
    except Exception as e:
        return jsonify({"success": False, "message": str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "ok", "model_loaded": True})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001, debug=True)