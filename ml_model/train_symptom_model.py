"""Train a symptom-only screening support model from the named 19-column CSV."""
import csv
import hashlib
import json
from pathlib import Path

import joblib
from sklearn.feature_extraction import DictVectorizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, precision_recall_fscore_support
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline

ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / 'data' / 'symptom_based_vitamin_deficiency_dataset_final.csv'
OUTPUT = ROOT / 'version_20261004_symptoms'
TARGET = 'Predicted Deficiency'
CATEGORICAL = ('Gender', 'Diet Type', 'Living Environment', 'Skin Condition')
FEATURE_COLS = ('Age', 'Gender', 'Diet Type', 'Living Environment',
                'Night Blindness', 'Dry Eyes', 'Bleeding Gums', 'Fatigue',
                'Tingling Sensation', 'Low Sun Exposure', 'Reduced Memory Capacity',
                'Shortness of Breath', 'Loss of Appetite', 'Fast Heart Rate',
                'Brittle Nails', 'Weight Loss', 'Reduced Wound Healing Capacity',
                'Skin Condition')

def normalized(row):
    return {key: str(row[key]).strip() if key in CATEGORICAL else int(row[key])
            for key in FEATURE_COLS}

def scores(y_true, y_pred):
    precision, recall, f1, _ = precision_recall_fscore_support(
        y_true, y_pred, average='macro', zero_division=0)
    return {'accuracy': round(float(accuracy_score(y_true, y_pred)), 4),
            'precision': round(float(precision), 4),
            'recall': round(float(recall), 4),
            'f1_score': round(float(f1), 4), 'count': len(y_true)}

def main():
    raw = SOURCE.read_bytes()
    with SOURCE.open(newline='', encoding='utf-8-sig') as handle:
        rows = list(csv.DictReader(handle))
    if set(rows[0]) != set(FEATURE_COLS) | {TARGET}:
        raise ValueError('CSV columns differ from the audited 19-column source.')
    data = [normalized(row) for row in rows]
    labels = [row[TARGET].strip() for row in rows]
    train_idx, test_idx = train_test_split(
        list(range(len(rows))), test_size=0.25, random_state=42, stratify=labels)
    model = Pipeline([
        ('vectorizer', DictVectorizer(sparse=False)),
        ('classifier', RandomForestClassifier(n_estimators=250, min_samples_leaf=2,
                                              random_state=42, n_jobs=1)),
    ])
    model.fit([data[i] for i in train_idx], [labels[i] for i in train_idx])
    actual = [labels[i] for i in test_idx]
    predicted = model.predict([data[i] for i in test_idx]).tolist()
    overall = scores(actual, predicted)
    school_positions = [pos for pos, i in enumerate(test_idx) if 5 <= data[i]['Age'] <= 19]
    school = scores([actual[i] for i in school_positions],
                    [predicted[i] for i in school_positions])
    OUTPUT.mkdir(exist_ok=True)
    joblib.dump(model, OUTPUT / 'model.joblib')
    metadata = {'best_model': 'Random Forest (symptom CSV)',
                'feature_cols': list(FEATURE_COLS),
                'deficiency_classes': sorted(set(labels)),
                'source_file': SOURCE.name, 'source_sha256': hashlib.sha256(raw).hexdigest(),
                'total_rows': len(rows), 'train_rows': len(train_idx),
                'test_rows': len(test_idx), 'split_seed': 42,
                'test_metrics': overall, 'school_age_test_metrics': school,
                'limitations': 'Single Kaggle dataset; no local learner validation. Do not diagnose.'}
    (OUTPUT / 'metadata.json').write_text(json.dumps(metadata, indent=2), encoding='utf-8')
    (OUTPUT / 'model_comparison.json').write_text(json.dumps([{
        'model': metadata['best_model'], **{k: v for k, v in overall.items() if k != 'count'}
    }], indent=2), encoding='utf-8')
    print(json.dumps(metadata, indent=2))

if __name__ == '__main__':
    main()
