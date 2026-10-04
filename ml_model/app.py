"""Local symptom-based screening service. Scores are prompts for nurse review."""
import glob
import json
import os

import joblib
from flask import Flask, jsonify, request

app = Flask(__name__)
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
VERSION_DIR = sorted(glob.glob(os.path.join(BASE_DIR, 'version_*')), reverse=True)[0]
with open(os.path.join(VERSION_DIR, 'metadata.json'), encoding='utf-8') as handle:
    meta = json.load(handle)
model = joblib.load(os.path.join(VERSION_DIR, 'model.joblib'))
FEATURE_COLS = meta['feature_cols']
BEST_MODEL = meta['best_model']

CATEGORICAL = {
    'Gender': ('Male', 'Female'),
    'Diet Type': ('Vegetarian', 'Non-Vegetarian'),
    'Living Environment': ('Rural', 'Urban'),
    'Skin Condition': ('Normal', 'Dry Skin', 'Rough Skin', 'Pale/Yellow Skin'),
}
BINARY = [key for key in FEATURE_COLS if key not in CATEGORICAL and key != 'Age']
FOODS = {
    'Iron': 'Malunggay, kangkong, beans, meat, sardines',
    'Vitamin A': 'Squash, carrots, malunggay, eggs',
    'Vitamin B12': 'Fish, eggs, dairy, meat',
    'Vitamin C': 'Guava, citrus, tomato, vegetables',
    'Vitamin D': 'Fortified milk, sardines, eggs',
    'Zinc': 'Beans, seafood, meat, seeds',
    'No Deficiency': 'Varied meals with vegetables, fruit and protein',
}

def build_feature_vector(data):
    if not isinstance(data, dict):
        raise ValueError('A JSON assessment object is required.')
    missing = [key for key in FEATURE_COLS if key not in data or data[key] in (None, '')]
    if missing:
        raise ValueError('Missing model inputs: ' + ', '.join(missing))
    result = {}
    for key, values in CATEGORICAL.items():
        value = str(data[key]).strip()
        if value not in values:
            raise ValueError('Invalid ' + key + '.')
        result[key] = value
    try:
        age = int(data['Age'])
    except (TypeError, ValueError):
        raise ValueError('Age must be a whole number.') from None
    if age < 5 or age > 69:
        raise ValueError("Age is outside this dataset's 5-69 year range.")
    result['Age'] = age
    for key in BINARY:
        if type(data[key]) is not int or data[key] not in (0, 1):
            raise ValueError(key + ' must be 0 or 1.')
        result[key] = data[key]
    return [result]

@app.route('/predict', methods=['POST'])
def predict():
    try:
        features = build_feature_vector(request.get_json(silent=True))
        label = str(model.predict(features)[0])
        probability = float(max(model.predict_proba(features)[0]))
        symptom_count = sum(features[0][key] for key in BINARY if key != 'Low Sun Exposure')
        priority = 'High' if symptom_count >= 4 else 'Moderate' if symptom_count >= 2 or label != 'No Deficiency' else 'Low'
        if label == 'Iron':
            recommendation = ('Possible iron-related concern for nurse review. Discuss iron-rich foods. '
                              'Ferrous sulfate only if separately assessed and authorized under clinic protocol.')
        elif label == 'No Deficiency':
            recommendation = 'No concern flagged by this dataset model. Continue usual clinical review.'
        else:
            recommendation = f'Possible {label.lower()}-related concern for nurse review; consider dietary counseling or referral as appropriate.'
        return jsonify({'success': True, 'predicted_deficiency': label,
                        'predicted_risk_level': priority, 'confidence_score': round(probability, 4),
                        'algorithm_used': BEST_MODEL, 'recommendation_text': recommendation,
                        'recommended_foods': FOODS[label], 'intervention_type': 'Nurse Review'})
    except (ValueError, TypeError, KeyError) as exc:
        return jsonify({'success': False, 'message': str(exc)}), 400
    except Exception:
        app.logger.exception('Prediction failed')
        return jsonify({'success': False, 'message': 'Prediction failed.'}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model': BEST_MODEL, 'version': os.path.basename(VERSION_DIR)})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5001, debug=False, use_reloader=False)
