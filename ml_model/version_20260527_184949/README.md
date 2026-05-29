# ClinicDesk ML Model Export

## Model Information
- **Model Type**: Decision Tree
- **Export Date**: 20260527_184949
- **Number of Features**: 33
- **F1-Score**: 0.9810
- **Accuracy**: 0.9812

## Files Included
- `Decision_Tree_model.joblib` - Trained model
- `scaler.joblib` - StandardScaler for feature normalization
- `target_encoder.pkl` - LabelEncoder for target variable
- `label_encoders.pkl` - Encoders for categorical features
- `feature_columns.json` - List of feature columns
- `model_metrics.json` - Performance metrics
- `requirements.txt` - Required Python packages
- `predict_example.py` - Example usage

## Usage in Flask API
Load the model and scaler, then use the feature columns to preprocess input before prediction.
