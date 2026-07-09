from flask import Flask, request, jsonify
import joblib
import numpy as np
import json
import os
import glob

app = Flask(__name__)

# ── Load latest model version ──
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
versions = sorted(glob.glob(os.path.join(BASE_DIR, 'version_*')), reverse=True)
if not versions:
    raise RuntimeError('No model version found. Run the training notebook first.')

VERSION_DIR = versions[0]
print(f'Loading model from: {VERSION_DIR}')

model     = joblib.load(os.path.join(VERSION_DIR, 'best_model.pkl'))
scaler    = joblib.load(os.path.join(VERSION_DIR, 'scaler.pkl'))
le_def    = joblib.load(os.path.join(VERSION_DIR, 'label_encoder_def.pkl'))
le_risk   = joblib.load(os.path.join(VERSION_DIR, 'label_encoder_risk.pkl'))

with open(os.path.join(VERSION_DIR, 'metadata.json')) as f:
    meta = json.load(f)

with open(os.path.join(VERSION_DIR, 'recommendations.json'), encoding='utf-8') as f:
    RECOMMENDATIONS = json.load(f)

FEATURE_COLS  = meta['feature_cols']
BEST_MODEL    = meta['best_model']
USES_SCALER   = meta['uses_scaler']

DIET_MAP     = {'balanced': 0, 'vegetarian': 1, 'high protein': 2, 'low calorie': 3, 'other': 4}
EXERCISE_MAP = {'sedentary': 0, 'light': 1, 'moderate': 2, 'active': 3}
SUN_MAP      = {'low': 0, 'moderate': 1, 'high': 2}


def build_feature_vector(data):
    """Map incoming PHP payload to the model feature vector."""
    gender = 1 if str(data.get('gender', 'Female')).strip().lower() in ('male', 'm') else 0
    diet   = DIET_MAP.get(str(data.get('diet_type', 'Balanced')).strip().lower(), 0)
    ex     = EXERCISE_MAP.get(str(data.get('exercise_level', 'Moderate')).strip().lower(), 2)
    sun    = SUN_MAP.get(str(data.get('sun_exposure', 'Moderate')).strip().lower(), 1)

    vec = [
        float(data.get('age', 13)),
        gender,
        float(data.get('bmi', 20)),
        ex, diet, sun,
        int(data.get('has_night_blindness', 0)),
        int(data.get('has_fatigue', 0)),
        int(data.get('has_bleeding_gums', 0)),
        int(data.get('has_bone_pain', 0)),
        int(data.get('has_muscle_weakness', 0)),
        int(data.get('has_numbness_tingling', 0)),
        int(data.get('has_memory_problems', 0)),
        int(data.get('has_pale_skin', 0)),
        int(data.get('has_multiple_deficiencies', 0)),
        int(data.get('symptoms_count', 0)),
        float(data.get('hemoglobin_g_dl', 12.5)),
        float(data.get('serum_vitamin_d_ng_ml', 25)),
        float(data.get('serum_vitamin_b12_pg_ml', 350)),
        float(data.get('serum_folate_ng_ml', 8)),
    ]
    return np.array(vec).reshape(1, -1)


@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json(force=True)
        X = build_feature_vector(data)

        if USES_SCALER:
            X = scaler.transform(X)

        pred_def  = model.predict(X)[0]
        deficiency = le_def.inverse_transform([pred_def])[0]

        # Confidence score from probability if available
        if hasattr(model, 'predict_proba'):
            proba = model.predict_proba(X)[0]
            confidence = round(float(np.max(proba)), 4)
        else:
            confidence = 0.75

        # Risk level: derive from BMI + deficiency severity
        bmi = float(data.get('bmi', 20))
        symptoms_count = int(data.get('symptoms_count', 0))
        if bmi < 16 or symptoms_count >= 4 or deficiency == 'Severe Malnutrition':
            risk = 'High'
        elif bmi < 18.5 or symptoms_count >= 2 or deficiency != 'Normal':
            risk = 'Moderate'
        else:
            risk = 'Low'

        recs = RECOMMENDATIONS.get(deficiency, RECOMMENDATIONS['Normal'])

        return jsonify({
            'success': True,
            'predicted_deficiency': deficiency,
            'predicted_risk_level': risk,
            'confidence_score': confidence,
            'algorithm_used': BEST_MODEL,
            'recommendation_text': recs['recommendation_text'],
            'recommended_foods': recs['recommended_foods'],
            'intervention_type': recs['intervention_type'],
        })

    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model': BEST_MODEL, 'version': os.path.basename(VERSION_DIR)})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001, debug=False)