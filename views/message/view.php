<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\GlobalHelper;

//if a user is not an admin and is not the one that created a message, then prevent the user from accessing the page
if(GlobalHelper::CurrentUser('role') != 'admin')
{
    if(GlobalHelper::CurrentUser('id') != $message->created_by)
    {
        exit('403 - You are forbidden from accessing this resource');
    }
}


$this->title = 'Intelligence Report #' . $message->id;
?>

<div class="message-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-text-fill text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted small mb-0">Detailed analysis and intelligence extraction</p>
        </div>
        <div>
            <a href="<?= Url::to(['message/index']) ?>" class="btn btn-outline-secondary shadow-sm me-2">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="<?= Url::to(['message/export-dossier', 'id' => $message->id]) ?>" class="btn btn-dark shadow-sm me-2">
                <i class="bi bi-file-pdf me-1"></i> Export Dossier
            </a>
            <?php if ($message->status == 'pending' || $message->status == 'failed'): ?>
            <a href="<?= Url::to(['message/analyze', 'id' => $message->id]) ?>" class="btn btn-primary shadow-sm" data-method="post">
                <i class="bi bi-cpu me-1"></i> Run Analysis
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Content & Analysis -->
        <div class="col-lg-8">
            <!-- Encrypted Content -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary"><i class="bi bi-file-earmark-lock me-2"></i>Encrypted Content</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message->encrypted_content)): ?>
                        <pre class="bg-light p-3 rounded mb-0" style="white-space: pre-wrap; font-family: 'Courier New', Courier, monospace;"><?= Html::encode($message->encrypted_content) ?></pre>
                    <?php elseif ($message->status === 'analyzing'): ?>
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p class="text-muted">Extracting content from <?= $model->file_type ?>...</p>
                        </div>
                    <?php elseif ($message->file_path): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i> This is a <strong><?= ucfirst($message->file_type) ?></strong> file. Content will be extracted during analysis.
                        </div>
                    <?php else: ?>
                        <p class="text-muted italic mb-0">No content available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Decrypted Content -->
            <?php if ($message->decrypted_content): ?>
            <div class="card shadow-sm border-0 mb-4 border-start border-4 border-success">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-success"><i class="bi bi-unlock me-2"></i>Decrypted Content</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded mb-0" style="white-space: pre-wrap; font-family: 'Courier New', Courier, monospace;"><?= Html::encode($message->decrypted_content) ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Intelligence Reports -->
            <?php if (!empty($analysisResults)): ?>
                <?php foreach ($analysisResults as $result): ?>
                    <?php if ($result->ai_insights): ?>
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-robot me-2"></i>AI Intelligence Report</h5>
                            <span class="badge bg-white text-primary">Confidence: <?= $result->confidence_score ?>%</span>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                                // Parse the insights string into structured sections
                                $insights = Html::encode($result->ai_insights);
                                $sections = [];
                                
                                // Split by common headers
                                if (preg_match_all('/^(LANGUAGE|CIPHER|TRANSLATION|ANALYSIS|TRANSCRIPTION|EXTRACTED TEXT):\s*(.+?)(?=^[A-Z]+:|$)/ms', $insights, $matches)) {
                                    for ($i = 0; $i < count($matches[1]); $i++) {
                                        $sections[] = [
                                            'title' => $matches[1][$i],
                                            'content' => trim($matches[2][$i])
                                        ];
                                    }
                                } else {
                                    // Fallback if no structured format
                                    $sections[] = ['title' => 'Analysis', 'content' => $insights];
                                }
                            ?>
                            
                            <?php foreach ($sections as $idx => $section): ?>
                                <div class="insight-section <?= $idx > 0 ? 'border-top' : '' ?> p-4">
                                    <div class="d-flex align-items-start">
                                        <div class="insight-icon me-3 mt-1">
                                            <?php 
                                                $iconClass = 'bi-info-circle';
                                                $bgColor = 'bg-primary';
                                                switch ($section['title']) {
                                                    case 'LANGUAGE': $iconClass = 'bi-translate'; $bgColor = 'bg-info'; break;
                                                    case 'CIPHER': $iconClass = 'bi-lock'; $bgColor = 'bg-warning'; break;
                                                    case 'TRANSLATION': $iconClass = 'bi-chat-left-text'; $bgColor = 'bg-success'; break;
                                                    case 'ANALYSIS': $iconClass = 'bi-lightbulb'; $bgColor = 'bg-danger'; break;
                                                    case 'TRANSCRIPTION': $iconClass = 'bi-mic'; $bgColor = 'bg-secondary'; break;
                                                    case 'EXTRACTED TEXT': $iconClass = 'bi-file-text'; $bgColor = 'bg-primary'; break;
                                                }
                            ?>
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle <?= $bgColor ?> text-white" style="width: 40px; height: 40px;">
                                                <i class="bi <?= $iconClass ?>"></i>
                                            </span>
                                        </div>
                                        <div class="insight-content flex-grow-1">
                                            <h6 class="fw-bold text-dark mb-2"><?= $section['title'] ?></h6>
                                            <p class="mb-0 text-muted" style="line-height: 1.6; white-space: pre-wrap; word-break: break-word;"><?= $section['content'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-light border-0 text-muted small d-flex justify-content-between">
                            <span><i class="bi bi-calendar3 me-1"></i> Analyzed: <?= date('M d, Y H:i', strtotime($result->analyzed_at)) ?></span>
                            <span><i class="bi bi-stopwatch me-1"></i> Processed in <?= $result->processing_time ?>s</span>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <style>
                .insight-section {
                    transition: background-color 0.3s ease;
                }
                .insight-section:hover {
                    background-color: #f8f9fa;
                }
                .insight-icon {
                    flex-shrink: 0;
                }
                .insight-content h6 {
                    color: #2c3e50;
                    font-size: 0.95rem;
                    letter-spacing: 0.3px;
                }
                .insight-content p {
                    font-size: 0.95rem;
                    color: #555;
                }
            </style>
        </div>

        <!-- Right Column: Metadata & Stats -->
        <div class="col-lg-4">
            <!-- Status & Metadata -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Metadata</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Status</span>
                            <?php
                            $class = 'bg-secondary';
                            switch ($message->status) {
                                case 'analyzed': case 'decrypted': $class = 'bg-success'; break;
                                case 'analyzing': $class = 'bg-primary'; break;
                                case 'failed': $class = 'bg-danger'; break;
                            }
                            ?>
                            <span class="badge <?= $class ?> rounded-pill px-3"><?= ucfirst($message->status) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Type</span>
                            <span class="fw-medium"><?= ucfirst($message->file_type ?: 'Text') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Encryption</span>
                            <span class="fw-medium"><?= Html::encode($message->encryption_type ?: 'Unknown') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">Intercepted</span>
                            <span class="fw-medium"><?= date('M d, H:i', strtotime($message->intercepted_at)) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Statistical Analysis -->
            <?php if ($frequencyAnalysis): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6 border-end">
                            <div class="small text-muted mb-1">I.C.</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($frequencyAnalysis->index_of_coincidence, 4) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1">Entropy</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($frequencyAnalysis->entropy, 2) ?></div>
                        </div>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="frequencyChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($frequencyAnalysis): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const freqData = <?= $frequencyAnalysis->character_frequencies ?>;
    const labels = Object.keys(freqData);
    const values = Object.values(freqData);

    const ctx = document.getElementById('frequencyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Frequency (%)',
                data: values,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
<?php endif; ?>