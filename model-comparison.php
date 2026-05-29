<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ClinicDesk | Model Comparison & Prediction Tester</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --clinic-primary: #0f766e;
            --clinic-secondary: #14b8a6;
            --clinic-border: #d9eef0;
            --clinic-text: #16323f;
            --clinic-muted: #6b7d87;
            --clinic-shadow: 0 12px 32px rgba(15,118,110,0.10);
            --clinic-radius: 22px;
        }
        body {
            background: #f4f8fb;
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: var(--clinic-text);
        }
        .wrapper {
            max-width: 1400px;
            margin: 35px auto;
            padding: 20px;
        }
        .header-box {
            background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-secondary));
            color: white;
            padding: 28px;
            border-radius: 18px;
            margin-bottom: 24px;
        }
        .card {
            border: none;
            border-radius: var(--clinic-radius);
            box-shadow: var(--clinic-shadow);
            margin-bottom: 24px;
        }
        .table th {
            background: #eef4f7;
            text-align: center;
        }
        .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-green {
            background: var(--clinic-primary);
            color: white;
            font-weight: 600;
        }
        .btn-green:hover {
            background: #0d5f58;
            color: white;
        }
        .best-model-badge {
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .chart-box {
            height: 350px;
        }
        .form-control, .form-select {
            border-radius: 14px;
            border: 1px solid var(--clinic-border);
        }
        .prediction-result {
            background: #f0fdfa;
            border-left: 5px solid var(--clinic-primary);
            border-radius: 16px;
            padding: 20px;
        }
    </style>
</head>
<body>
<div id="app" class="wrapper">
    <div class="header-box d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="fw-bold mb-2">📊 Model Comparison & Prediction Tester</h1>
            <p class="mb-0">Objective 2: Compare ML algorithms and determine the best for nutritional risk classification</p>
        </div>
        <a href="nurse-dashboard.php" class="btn btn-light">← Back to Dashboard</a>
    </div>

    <div v-if="loading" class="alert alert-info">Loading model metrics...</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Comparison Table -->
    <div class="card p-4" v-if="metrics.length">
        <h3 class="fw-bold mb-3">📈 Algorithm Performance Comparison</h3>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Accuracy</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>F1-Score</th>
                        <th>Best Model</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="m in metrics" :key="m.model">
                        <td class="fw-bold">{{ m.model }}</td>
                        <td>{{ (m.accuracy * 100).toFixed(2) }}%</td>
                        <td>{{ (m.precision * 100).toFixed(2) }}%</td>
                        <td>{{ (m.recall * 100).toFixed(2) }}%</td>
                        <td>{{ (m.f1_score * 100).toFixed(2) }}%</td>
                        <td>
                            <span v-if="m.f1_score === bestF1" class="best-model-badge">✓ Best</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-box">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success">
                    <strong>🏆 Best Model:</strong> {{ bestModelName }} (F1-Score: {{ (bestF1 * 100).toFixed(2) }}%)<br>
                    This model is deployed for real‑time predictions.
                </div>
            </div>
        </div>
    </div>

    <!-- Prediction Tester -->
    <div class="card p-4" v-if="metrics.length">
        <h3 class="fw-bold mb-3">🔮 Test Prediction (Using Best Model)</h3>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Age</label>
                <input type="number" class="form-control" v-model.number="testData.age">
            </div>
            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select class="form-select" v-model="testData.gender">
                <option :value="0">Male</option>
                <option :value="1">Female</option>
            </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">BMI</label>
                <input type="number" step="0.1" class="form-control" v-model.number="testData.bmi">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fatigue?</label>
                <select class="form-select" v-model="testData.has_fatigue">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Night Blindness?</label>
                <select class="form-select" v-model="testData.has_night_blindness">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bleeding Gums?</label>
                <select class="form-select" v-model="testData.has_bleeding_gums">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bone Pain?</label>
                <select class="form-select" v-model="testData.has_bone_pain">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Pale Skin?</label>
                <select class="form-select" v-model="testData.has_pale_skin">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-end">
            <button class="btn btn-green" @click="runPrediction" :disabled="predicting">
                {{ predicting ? 'Predicting...' : 'Get Prediction' }}
            </button>
        </div>

        <div v-if="predictionResult" class="prediction-result mt-4">
            <h5 class="fw-bold">Prediction Result</h5>
            <p><strong>Predicted Deficiency:</strong> {{ predictionResult.predicted_deficiency }}</p>
            <p><strong>Risk Level:</strong> 
                <span :class="{'text-danger': predictionResult.predicted_risk_level === 'High', 'text-warning': predictionResult.predicted_risk_level === 'Moderate', 'text-success': predictionResult.predicted_risk_level === 'Low'}">
                    {{ predictionResult.predicted_risk_level }}
                </span>
            </p>
            <p><strong>Confidence:</strong> {{ (predictionResult.confidence_score * 100).toFixed(2) }}%</p>
            <p><strong>Recommendation:</strong> {{ predictionResult.recommendation_text }}</p>
            <p><strong>Recommended Foods:</strong> {{ predictionResult.recommended_foods }}</p>
        </div>
        <div v-if="predictionError" class="alert alert-danger mt-4">{{ predictionError }}</div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            loading: true,
            error: null,
            metrics: [],
            bestModelName: '',
            bestF1: 0,
            chartInstance: null,
            predicting: false,
            predictionResult: null,
            predictionError: null,
            testData: {
                age: 15,
                gender: 1,   // 1 = Female, 0 = Male (based on typical encoder)
                bmi: 18.5,
                has_fatigue: 0,
                has_night_blindness: 0,
                has_bleeding_gums: 0,
                has_bone_pain: 0,
                has_pale_skin: 0
            }
        };
    },
    async mounted() {
        await this.loadMetrics();
        this.renderChart();
    },
    methods: {
        async loadMetrics() {
            try {
                // Find the latest model version folder. Here we assume the metrics JSON is inside ml_model/version_*/model_comparison.json
                // For simplicity, we'll fetch from a fixed endpoint that returns the comparison JSON.
                // In production, you can read the JSON file directly. Here we use a PHP proxy to read the file.
                const response = await fetch('api/get_model_comparison.php');
                const data = await response.json();
                if (data.success) {
                    this.metrics = data.metrics;
                    // Find best model by F1-score
                    let best = this.metrics.reduce((max, m) => m.f1_score > max.f1_score ? m : max, this.metrics[0]);
                    this.bestModelName = best.model;
                    this.bestF1 = best.f1_score;
                } else {
                    this.error = data.message || 'Failed to load metrics';
                }
            } catch(e) {
                this.error = 'Error loading metrics: ' + e.message;
            } finally {
                this.loading = false;
            }
        },
        renderChart() {
            if (!this.metrics.length) return;
            const ctx = document.getElementById('comparisonChart').getContext('2d');
            if (this.chartInstance) this.chartInstance.destroy();
            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.metrics.map(m => m.model),
                    datasets: [
                        { label: 'Accuracy', data: this.metrics.map(m => m.accuracy), backgroundColor: '#0f766e' },
                        { label: 'F1-Score', data: this.metrics.map(m => m.f1_score), backgroundColor: '#14b8a6' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: { y: { beginAtZero: true, max: 1, ticks: { callback: v => (v*100)+'%' } } },
                    plugins: { tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${(ctx.raw*100).toFixed(2)}%` } } }
                }
            });
        },
        async runPrediction() {
            this.predicting = true;
            this.predictionResult = null;
            this.predictionError = null;
            try {
                // Build payload similar to generate_student_prediction.php but simpler
                const payload = {
                    age: this.testData.age,
                    gender: this.testData.gender,
                    bmi: this.testData.bmi,
                    has_fatigue: this.testData.has_fatigue,
                    has_night_blindness: this.testData.has_night_blindness,
                    has_bleeding_gums: this.testData.has_bleeding_gums,
                    has_bone_pain: this.testData.has_bone_pain,
                    has_pale_skin: this.testData.has_pale_skin
                };
                const response = await fetch('http://127.0.0.1:5001/predict', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    this.predictionResult = result;
                } else {
                    this.predictionError = result.message || 'Prediction failed';
                }
            } catch(e) {
                this.predictionError = 'Error: ' + e.message;
            }
            this.predicting = false;
        }
    }
}).mount('#app');
</script>
</body>
</html>