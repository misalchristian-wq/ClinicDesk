<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ClinicDesk | Model Performance & Prediction Tester</title>
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
            <h1 class="fw-bold mb-2">📊 Model Performance & Prediction Tester</h1>
            <p class="mb-0">Review the current symptom model and test its 18 input fields.</p>
        </div>
        <a href="nurse-dashboard.php" class="btn btn-light">← Back to Dashboard</a>
    </div>

    <div v-if="loading" class="alert alert-info">Loading model metrics...</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Comparison Table -->
    <div class="card p-4" v-if="metrics.length">
        <h3 class="fw-bold mb-3">📈 Source Dataset Holdout Performance</h3>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Accuracy</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>F1-Score</th>
                        <th>Status</th>
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
                            <span v-if="m.f1_score === bestF1" class="best-model-badge">Current</span>
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
                <div class="alert alert-warning">
                    <strong>Current model:</strong> {{ bestModelName }} (macro F1: {{ (bestF1 * 100).toFixed(2) }}%)<br>
                    This score comes from the source CSV holdout, not ClinicDesk learners. Only 37 school-age rows were in the holdout; their accuracy was 21.62%. Use results only as prompts for nurse review.
                </div>
            </div>
        </div>
    </div>

    <!-- Prediction Tester -->
    <div class="card p-4" v-if="metrics.length">
        <h3 class="fw-bold mb-3">🔮 Test Current Model</h3>
        <p class="text-muted">Enter the 18 predictor columns from the symptom CSV. Predicted Deficiency is the model's output and is not an input. This demonstration does not save a student prediction.</p>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Age</label>
                <input type="number" class="form-control" v-model.number="testData.age">
            </div>
            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select class="form-select" v-model="testData.gender">
                <option value="">Select</option><option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
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
            <div class="col-md-3"><label class="form-label">Diet type</label><select class="form-select" v-model="testData['Diet Type']"><option value="">Select</option><option>Vegetarian</option><option>Non-Vegetarian</option></select></div>
            <div class="col-md-3"><label class="form-label">Living environment</label><select class="form-select" v-model="testData['Living Environment']"><option value="">Select</option><option>Rural</option><option>Urban</option></select></div>
            <div class="col-md-3"><label class="form-label">Skin condition</label><select class="form-select" v-model="testData['Skin Condition']"><option value="">Select</option><option>Normal</option><option>Dry Skin</option><option>Rough Skin</option><option>Pale/Yellow Skin</option></select></div>
            <div class="col-md-3" v-for="item in additionalFlags" :key="item.key"><label class="form-label">{{ item.label }}</label><select class="form-select" v-model.number="testData[item.key]"><option :value="0">No</option><option :value="1">Yes</option></select></div>
        </div>
        <div class="mt-4 d-flex justify-content-end">
            <button class="btn btn-green" @click="runPrediction" :disabled="predicting || missingTestInputs.length">
                {{ predicting ? 'Predicting...' : 'Get Prediction' }}
            </button>
        </div>
        <p v-if="missingTestInputs.length" class="small text-muted mt-2">Complete: {{ missingTestInputs.join(', ') }}.</p>

        <div v-if="predictionResult" class="prediction-result mt-4">
            <h5 class="fw-bold">Prediction Result</h5>
            <p><strong>Screening flag:</strong> {{ screeningFlag(predictionResult.predicted_deficiency) }}</p>
            <p><strong>Screening Priority:</strong>
                <span :class="{'text-danger': predictionResult.predicted_risk_level === 'High', 'text-warning': predictionResult.predicted_risk_level === 'Moderate', 'text-success': predictionResult.predicted_risk_level === 'Low'}">
                    {{ predictionResult.predicted_risk_level }}
                </span>
            </p>
            <p><strong>Model score:</strong> {{ (predictionResult.confidence_score * 100).toFixed(2) }}%</p>
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
            additionalFlags: [
                {key:'Dry Eyes',label:'Dry eyes?'},
                {key:'Tingling Sensation',label:'Tingling sensation?'},
                {key:'Low Sun Exposure',label:'Low sun exposure?'},
                {key:'Reduced Memory Capacity',label:'Reduced memory capacity?'},
                {key:'Shortness of Breath',label:'Shortness of breath?'},
                {key:'Loss of Appetite',label:'Loss of appetite?'},
                {key:'Fast Heart Rate',label:'Fast heart rate?'},
                {key:'Brittle Nails',label:'Brittle nails?'},
                {key:'Weight Loss',label:'Weight loss?'},
                {key:'Reduced Wound Healing Capacity',label:'Reduced wound healing capacity?'}
            ],
            testData: {
                age: '',
                gender: '',
                'Diet Type':'', 'Living Environment':'', 'Skin Condition':'',
                has_fatigue: 0,
                has_night_blindness: 0,
                has_bleeding_gums: 0,
                'Dry Eyes':0, 'Tingling Sensation':0, 'Low Sun Exposure':0,
                'Reduced Memory Capacity':0, 'Shortness of Breath':0,
                'Loss of Appetite':0, 'Fast Heart Rate':0, 'Brittle Nails':0,
                'Weight Loss':0, 'Reduced Wound Healing Capacity':0
            }
        };
    },
    computed: {
        missingTestInputs() {
            const fields = ['age','gender','Diet Type','Living Environment','Skin Condition'];
            return fields.filter(key => !this.testData[key]);
        }
    },
    async mounted() {
        await this.loadMetrics();
        this.renderChart();
    },
    methods: {
        screeningFlag(value) { return ({'Iron':'Possible iron-related concern','Vitamin A':'Possible vitamin A-related concern','Vitamin B12':'Possible vitamin B12-related concern','Vitamin C':'Possible vitamin C-related concern','Vitamin D':'Possible vitamin D-related concern','Zinc':'Possible zinc-related concern','No Deficiency':'No concern flagged by this model'})[value] || 'For nurse review'; },
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
            if (this.missingTestInputs.length) return;
            this.predicting = true;
            this.predictionResult = null;
            this.predictionError = null;
            try {
                const response = await fetch('api/test_model_prediction.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('local_id_token') || ''}` },
                    body: JSON.stringify({
                        Age: Number(this.testData.age), Gender: this.testData.gender,
                        'Diet Type': this.testData['Diet Type'],
                        'Living Environment': this.testData['Living Environment'],
                        'Skin Condition': this.testData['Skin Condition'],
                        'Night Blindness': Number(this.testData.has_night_blindness),
                        'Bleeding Gums': Number(this.testData.has_bleeding_gums),
                        Fatigue: Number(this.testData.has_fatigue),
                        ...Object.fromEntries(this.additionalFlags.map(item => [item.key, this.testData[item.key]]))
                    })
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
<script src="assets/table-pagination.js" defer></script>
</body>
</html>
