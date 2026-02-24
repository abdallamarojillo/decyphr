<?php
use yii\helpers\Html;
use yii\helpers\Json;

/** @var app\models\Log $log */

$this->title = 'Log Details #' . $log->id;
$this->params['breadcrumbs'][] = ['label' => 'System Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-uppercase fw-semibold tracking-wider">
                    <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>" class="text-decoration-none text-muted">Logs</a></li>
                    <li class="breadcrumb-item active text-primary">Details</li>
                </ol>
            </nav>
            <h3 class="fw-bold m-0 text-dark"><?= Html::encode($this->title) ?></h3>
        </div>
        <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Back to List', ['site/logs'], ['class' => 'btn btn-outline-secondary btn-sm px-3 rounded-pill']) ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">General Information</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Action</label>
                            <p class="fw-semibold text-dark"><?= Html::encode($log->action) ?></p>

                            <label class="text-muted small fw-bold text-uppercase mb-1">Log Type</label>
                            <div>
                                <span class="badge bg-<?= $log->log_type === 'Error' ? 'danger' : 'primary' ?>-subtle text-<?= $log->log_type === 'Error' ? 'danger' : 'primary' ?>-emphasis rounded-pill px-3">
                                    <?= Html::encode($log->log_type) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">User / Source</label>
                            <p class="text-dark">
                                <i class="fas fa-user-circle text-muted me-1"></i>
                                <?= $log->user_id ?? '<span class="text-muted italic">System</span>' ?>
                            </p>

                            <label class="text-muted small fw-bold text-uppercase mb-1">IP Address</label>
                            <p class="font-monospace small text-primary"><?= Html::encode($log->ip_address) ?></p>
                        </div>
                    </div>

                    <hr class="my-3 opacity-10">

                    <label class="text-muted small fw-bold text-uppercase mb-1">Description</label>
                    <p class="text-secondary"><?= Html::encode($log->action_description ?: 'No description provided.') ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
                    <h5 class="fw-bold mb-0">Associated Data Payload</h5>
                    <button class="btn btn-link btn-sm text-decoration-none" onclick="copyToClipboard()">Copy JSON</button>
                </div>
                <div class="card-body px-4 pb-4">
                    <pre id="jsonPayload" class="p-3 rounded-3 bg-dark text-info mb-0 small shadow-inner" style="max-height: 400px; overflow-y: auto;">
<?= Html::encode(Json::encode(Json::decode($log->associated_data), JSON_PRETTY_PRINT)) ?>
                    </pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Request Metadata</h5>
                </div>
                <div class="card-body px-4 pb-4 text-break">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">HTTP Method</label>
                        <span class="badge bg-dark px-2"><?= Html::encode($log->http_method) ?></span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">URL</label>
                        <code class="small text-break"><?= Html::encode($log->url) ?></code>
                    </div>

                    <div class="mb-0">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">User Agent</label>
                        <p class="small text-muted mb-0"><?= Html::encode($log->user_agent) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const text = document.getElementById('jsonPayload').innerText;
    navigator.clipboard.writeText(text);
    alert('JSON copied to clipboard!');
}
</script>