<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap5\ActiveForm;
use app\helpers\GlobalHelper;

$this->title = 'OSINT Intelligence Feed Request ID ' . $osintaidata[0]['request_id'];
$relatedCount = 0;
?>

<style>
:root {
    --intel-primary: #0d6efd;
    --intel-dark: #0f172a;
    --intel-soft: #f8fafc;
    --intel-border: #e2e8f0;
    --intel-muted: #64748b;
    --intel-success: #198754;
    --intel-warning: #f59e0b;
    --intel-danger: #dc3545;
}

.masked-value {
    word-break: break-word;
}

.intel-card {
    border: 0;
    border-radius: 1.25rem;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    background: #fff;
}

.intel-soft-card {
    border-radius: 1rem;
    border: 1px solid var(--intel-border);
    background: #fff;
}

.intel-section-label {
    letter-spacing: 1.2px;
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--intel-muted);
}

.intel-action-btn {
    border-radius: 999px;
    padding: 0.55rem 1rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.intel-action-btn:hover {
    transform: translateY(-1px);
}

.evidence-post-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1rem;
    transition: all 0.2s ease;
    background: #fff;
}

.evidence-post-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
}

.platform-chip {
    border-radius: 999px;
    padding: 0.45rem 0.85rem;
    font-weight: 700;
    font-size: 0.72rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.metric-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    border: 1px solid var(--intel-border);
    background: #fff;
    font-size: 0.8rem;
    color: #334155;
}

.deepdecode-hero {
    background: linear-gradient(135deg, #0d6efd 0%, #0f172a 100%);
    color: #fff;
    border-radius: 1.25rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.deepdecode-hero::after {
    content: "";
    position: absolute;
    right: -30px;
    top: -30px;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.07);
}

.decode-panel {
    border-radius: 1rem;
    border: 1px solid var(--intel-border);
    background: #fff;
    height: 100%;
}

.decode-panel .panel-head {
    padding: 1rem 1.2rem 0 1.2rem;
}

.decode-panel .panel-body {
    padding: 1rem 1.2rem 1.2rem 1.2rem;
}

.decode-block {
    border: 1px solid var(--intel-border);
    border-radius: 1rem;
    background: #fff;
    padding: 1rem;
    height: 100%;
}

.decode-block.soft {
    background: #f8fafc;
}

.decode-stat {
    border-radius: 1rem;
    background: #fff;
    border: 1px solid var(--intel-border);
    padding: 1rem;
    text-align: center;
}

.decode-stat .value {
    font-size: 1.4rem;
    font-weight: 800;
}

.decode-loading-wrap {
    border-radius: 1rem;
    background: #eff6ff;
    border: 1px dashed #93c5fd;
    color: #1d4ed8;
    padding: 1rem;
}

.fullscreen-decode-modal .modal-content {
    border: 0;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(15, 23, 42, 0.18);
}

.fullscreen-decode-modal .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.fullscreen-decode-modal .modal-body {
    background: #f8fafc;
}

.decode-quote {
    border-left: 4px solid #93c5fd;
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 1rem;
}

@media (min-width: 1200px) {
    .modal-fullscreen-xl-down .modal-body {
        padding: 2rem;
    }
}

.intel-tabs-wrap {
    background: #fff;
    border-radius: 1.25rem;
    padding: 1rem;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    border: 1px solid var(--intel-border);
}

.intel-tabs.nav-tabs {
    border-bottom: 1px solid var(--intel-border);
    gap: 0.75rem;
}

.intel-tabs.nav-tabs .nav-link {
    border: 0;
    border-radius: 999px;
    padding: 0.8rem 1.25rem;
    font-weight: 700;
    color: var(--intel-muted);
    background: #f8fafc;
    transition: all 0.2s ease;
}

.intel-tabs.nav-tabs .nav-link:hover {
    color: var(--intel-primary);
    background: #eff6ff;
}

.intel-tabs.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #0d6efd 0%, #0f172a 100%);
    color: #fff;
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.18);
}

.intel-tab-pane {
    padding-top: 1.5rem;
}

.chat-feed-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1rem;
    background: #fff;
    transition: all 0.2s ease;
}

.chat-feed-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
}

.chat-feed-text {
    border-left: 4px solid #93c5fd;
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 1rem;
    color: #334155;
}

.section-shell {
    background: #fff;
    border-radius: 1.25rem;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    border: 1px solid var(--intel-border);
}

.section-shell .section-head {
    padding: 1.25rem 1.5rem 0 1.5rem;
}

.section-shell .section-body {
    padding: 1.25rem 1.5rem 1.5rem 1.5rem;
}
</style>

<div class="container- py-4">

    <div class="intel-tabs-wrap mb-5">
        <ul class="nav nav-tabs intel-tabs- nav-justified" id="osintViewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="post-details-tab" data-bs-toggle="tab"
                    data-bs-target="#post-details-pane" type="button" role="tab" aria-controls="post-details-pane"
                    aria-selected="true">
                    <i class="bi bi-file-earmark-text me-2"></i>Post Details
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chats-tables-tab" data-bs-toggle="tab" data-bs-target="#chats-tables-pane"
                    type="button" role="tab" aria-controls="chats-tables-pane" aria-selected="false">
                    <i class="bi bi-chat-dots me-2"></i>Posts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="statistics-tables-tab" data-bs-toggle="tab"
                    data-bs-target="#statistics-tables-pane" type="button" role="tab"
                    aria-controls="statistics-tables-pane" aria-selected="false">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Statistics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="actions-tab" data-bs-toggle="tab" data-bs-target="#actions-pane"
                    type="button" role="tab" aria-controls="actions-pane" aria-selected="false">
                    <i class="bi bi-sliders me-2"></i>Actions
                </button>
            </li>
        </ul>

        <div class="tab-content" id="osintViewTabsContent">

            <div class="tab-pane fade show active intel-tab-pane" id="post-details-pane" role="tabpanel"
                aria-labelledby="post-details-tab">

                <div id="results-container" class="row g-4">
                    <?php foreach ($osintaidata as $model): ?>
                    <?php
                        $report = Json::decode($model->report);
                        if (!is_array($report)) {
                            $report = [];
                        }

                        $score = (int) $model->numerical_score;
                        $statusColor = ($score >= 70) ? 'danger' : (($score >= 40) ? 'warning' : 'success');
                        $statusLabel = ($score >= 70) ? 'CRITICAL' : (($score >= 40) ? 'ELEVATED' : 'STABLE');

                        $primaryLoc = !empty($report['localized_risks']) && !empty($report['localized_risks'][0]['location'])
                            ? $report['localized_risks'][0]['location']
                            : 'Kenya';

                        $mapEmbedUrl = "https://www.google.com/maps?q=" . urlencode($primaryLoc . ", Kenya") . "&output=embed";
                        $mapRedirectUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($primaryLoc . ", Kenya");
                    ?>
                    <div class="col-12">
                        <div class="card intel-card overflow-hidden mb-4">
                            <div class="row g-0">
                                <div
                                    class="col-md-1 bg-<?= $statusColor ?> d-flex flex-column justify-content-center align-items-center text-white py-4">
                                    <small class="fw-bold opacity-75 mb-2"
                                        style="writing-mode: vertical-rl; transform: rotate(180deg);">SCORE</small>
                                    <h2 class="fw-black mb-0"><?= $score ?></h2>
                                </div>

                                <div class="col-md-11">
                                    <div class="card border-0 shadow-sm rounded-4">
                                        <div class="card-body p-4 p-lg-5">

                                            <!-- Header -->
                                            <div
                                                class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                        <span class="badge rounded-pill bg-light text-dark border">
                                                            Request ID: <?= Html::encode($model->request_id) ?>
                                                        </span>
                                                        <span
                                                            class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?>">
                                                            <?= Html::encode($statusLabel) ?>
                                                        </span>
                                                    </div>

                                                    <h3 class="fw-bold mb-2 d-flex align-items-center gap-2">
                                                        <i class="bi bi-shield-exclamation text-danger"></i>
                                                        <span><?= Html::encode($model->keyword) ?></span>
                                                    </h3>

                                                    <div class="text-muted small">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Analysis Date:
                                                        <?= date('M d, Y - H:i', strtotime($model->analyzed_at)) ?>
                                                    </div>
                                                </div>

                                                <?php
                    $scoreRaw = (float)$score ?? 0;
                    $displayScore = $scoreRaw <= 10 ? $scoreRaw * 10 : $scoreRaw;
                    $scoreColor = ($displayScore > 70) ? 'danger' : (($displayScore > 40) ? 'warning' : 'success');
                ?>

                                                <div class="card border-0 bg-light rounded-4 flex-shrink-0"
                                                    style="min-width: 240px;">
                                                    <div class="card-body text-center">
                                                        <div class="text-uppercase text-muted small fw-semibold mb-2">
                                                            Threat Exposure</div>
                                                        <div class="display-6 fw-bold text-<?= $scoreColor ?> mb-2">
                                                            <?= $displayScore ?>%
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-<?= $scoreColor ?>"
                                                                role="progressbar" style="width: <?= $displayScore ?>%"
                                                                aria-valuenow="<?= $displayScore ?>" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>
                                                        <div class="small text-muted">
                                                            Trajectory:
                                                            <span class="fw-semibold text-dark">
                                                                <?= Html::encode($report['risk_trajectory'] ?? 'Stable') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Executive Summary -->
                                            <div class="card border-0 bg-light rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center gap-2 mb-3">
                                                        <div class="bg-primary-subtle text-primary rounded-3 px-3 py-2">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-uppercase text-muted small">
                                                                Executive Summary</h6>
                                                        </div>
                                                    </div>

                                                    <div class="text-dark lh-lg">
                                                        <?= nl2br(Html::encode($report['threat_summary'] ?? 'System status nominal. No immediate threats detected.')) ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tabs -->
                                            <ul class="nav nav-pills nav-justified gap-2 mb-4" id="intelTabs-<?= $model->id ?>"
                                                role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active rounded-pill px-4"
                                                        id="overview-tab-<?= $model->id ?>" data-bs-toggle="tab"
                                                        data-bs-target="#overview-pane-<?= $model->id ?>" type="button"
                                                        role="tab" aria-controls="overview-pane-<?= $model->id ?>"
                                                        aria-selected="true">
                                                        <i class="bi bi-geo-alt-fill me-2"></i>Location
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-pill px-4"
                                                        id="signals-tab-<?= $model->id ?>" data-bs-toggle="tab"
                                                        data-bs-target="#signals-pane-<?= $model->id ?>" type="button"
                                                        role="tab" aria-controls="signals-pane-<?= $model->id ?>"
                                                        aria-selected="false">
                                                        <i class="bi bi-broadcast me-2"></i>Signals
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-pill px-4"
                                                        id="methodology-tab-<?= $model->id ?>" data-bs-toggle="tab"
                                                        data-bs-target="#methodology-pane-<?= $model->id ?>"
                                                        type="button" role="tab"
                                                        aria-controls="methodology-pane-<?= $model->id ?>"
                                                        aria-selected="false">
                                                        <i class="bi bi-cpu me-2"></i>Methodology
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-pill px-4"
                                                        id="actions-tab-<?= $model->id ?>" data-bs-toggle="tab"
                                                        data-bs-target="#actions-pane-<?= $model->id ?>" type="button"
                                                        role="tab" aria-controls="actions-pane-<?= $model->id ?>"
                                                        aria-selected="false">
                                                        <i class="bi bi-list-check me-2"></i>Interventions
                                                    </button>
                                                </li>
                                            </ul>

                                            <div class="tab-content" id="intelTabsContent-<?= $model->id ?>">

                                                <!-- Overview Tab -->
                                                <div class="tab-pane fade show active"
                                                    id="overview-pane-<?= $model->id ?>" role="tabpanel"
                                                    aria-labelledby="overview-tab-<?= $model->id ?>">

                                                    <div class="row g-4">
                                                        <div class="col-lg-7">
                                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                                <div class="card-body p-4">
                                                                    <div
                                                                        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                                                                        <div>
                                                                            <h5 class="mb-1 fw-bold">Geographic Threat
                                                                                Vectors</h5>
                                                                            <p class="text-muted small mb-0">Highlighted
                                                                                risk locations and situational context.
                                                                            </p>
                                                                        </div>

                                                                        <button
                                                                            class="btn btn-outline-secondary btn-sm rounded-pill"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#map-view-<?= $model->id ?>"
                                                                            aria-expanded="false"
                                                                            aria-controls="map-view-<?= $model->id ?>">
                                                                            <i class="bi bi-map me-1"></i> Toggle Map
                                                                        </button>
                                                                    </div>

                                                                    <div class="collapse mb-4"
                                                                        id="map-view-<?= $model->id ?>">
                                                                        <div class="border rounded-4 overflow-hidden">
                                                                            <div style="height: 260px;">
                                                                                <iframe width="100%" height="100%"
                                                                                    frameborder="0" loading="lazy"
                                                                                    src="<?= Html::encode($mapEmbedUrl) ?>"
                                                                                    allowfullscreen></iframe>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mt-3">
                                                                            <a href="<?= Html::encode($mapRedirectUrl) ?>"
                                                                                target="_blank"
                                                                                class="btn btn-primary btn-sm rounded-pill">
                                                                                <i
                                                                                    class="bi bi-box-arrow-up-right me-1"></i>
                                                                                Open in Maps
                                                                            </a>
                                                                        </div>
                                                                    </div>

                                                                    <?php $risks = $report['localized_risks'] ?? []; ?>
                                                                    <?php if (!empty($risks)): ?>
                                                                    <div class="vstack gap-3">
                                                                        <?php foreach (array_slice((array) $risks, 0, 3) as $risk): ?>
                                                                        <?php
                                                    $severity = $risk['severity'] ?? '';
                                                    $severityColor = ($severity === 'High') ? 'danger' : (($severity === 'Medium') ? 'warning' : 'secondary');
                                                ?>
                                                                        <div class="border rounded-4 p-3">
                                                                            <div
                                                                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2 mb-2">
                                                                                <div class="fw-semibold text-dark">
                                                                                    <i
                                                                                        class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                                    <?= Html::encode($risk['location'] ?? 'Unknown') ?>
                                                                                </div>
                                                                                <span
                                                                                    class="badge bg-<?= $severityColor ?>-subtle text-<?= $severityColor ?> border border-<?= $severityColor ?>">
                                                                                    <?= Html::encode($severity ?: 'Unspecified') ?>
                                                                                </span>
                                                                            </div>

                                                                            <p class="text-muted small mb-2">
                                                                                <?= Html::encode($risk['risk_description'] ?? '') ?>
                                                                            </p>

                                                                            <?php if (!empty($risk['location'])): ?>
                                                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(($risk['location'] ?? '') . ', Kenya') ?>"
                                                                                target="_blank"
                                                                                class="btn btn-link btn-sm p-0 text-decoration-none">
                                                                                <i class="bi bi-compass me-1"></i>
                                                                                Navigate to location
                                                                            </a>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <div class="text-center py-5">
                                                                        <div class="mb-3">
                                                                            <i
                                                                                class="bi bi-shield-check text-success display-6"></i>
                                                                        </div>
                                                                        <h6 class="fw-semibold mb-1">No localized risks
                                                                            identified</h6>
                                                                        <p class="text-muted small mb-0">No specific
                                                                            geographic hotspots were detected in this
                                                                            analysis.</p>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-5">
                                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                                <div class="card-body p-4">
                                                                    <h5 class="fw-bold mb-1">Quick Insight</h5>
                                                                    <p class="text-muted small mb-4">A compact summary
                                                                        of system interpretation and status.</p>

                                                                    <div class="list-group list-group-flush">
                                                                        <div class="list-group-item px-0">
                                                                            <div
                                                                                class="small text-muted text-uppercase fw-bold mb-1">
                                                                                Threat Score</div>
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= $displayScore ?>%</div>
                                                                        </div>
                                                                        <div class="list-group-item px-0">
                                                                            <div
                                                                                class="small text-muted text-uppercase fw-bold mb-1">
                                                                                Risk Trajectory</div>
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= Html::encode($report['risk_trajectory'] ?? 'Stable') ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="list-group-item px-0">
                                                                            <div
                                                                                class="small text-muted text-uppercase fw-bold mb-1">
                                                                                Primary Keyword</div>
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= Html::encode($model->keyword) ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="list-group-item px-0">
                                                                            <div
                                                                                class="small text-muted text-uppercase fw-bold mb-1">
                                                                                Localized Risks Found</div>
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= count((array) ($report['localized_risks'] ?? [])) ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="list-group-item px-0">
                                                                            <div
                                                                                class="small text-muted text-uppercase fw-bold mb-1">
                                                                                Decoded Terms Found</div>
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= count((array) ($report['decoded_language'] ?? [])) ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Signals Tab -->
                                                <div class="tab-pane fade" id="signals-pane-<?= $model->id ?>"
                                                    role="tabpanel" aria-labelledby="signals-tab-<?= $model->id ?>">

                                                    <div class="row g-4">
                                                        <div class="col-lg-6">
                                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                                <div class="card-body p-4">
                                                                    <div class="mb-4">
                                                                        <h5 class="fw-bold mb-1">Decoded Terms</h5>
                                                                        <p class="text-muted small mb-0">Detected
                                                                            terminology and interpreted meaning.</p>
                                                                    </div>

                                                                    <?php if (!empty($report['decoded_language'])): ?>
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        <?php foreach ((array) ($report['decoded_language'] ?? []) as $lang): ?>
                                                                        <span
                                                                            class="badge text-bg-light border rounded-pill px-3 py-2 text-wrap fw-normal"
                                                                            title="<?= Html::encode($lang['contextual_explanation'] ?? '') ?>">
                                                                            <span class="fw-semibold text-primary">
                                                                                <?= Html::encode($lang['original_term'] ?? '') ?>
                                                                            </span>
                                                                            <i class="bi bi-arrow-right mx-1"></i>
                                                                            <?= Html::encode($lang['decoded_meaning'] ?? '') ?>
                                                                        </span>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <p class="text-muted small mb-0">No decoded terms
                                                                        available.</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                                                <div class="card-body p-4">
                                                                    <div class="mb-4">
                                                                        <h5 class="fw-bold mb-1">Surveillance Protocol
                                                                        </h5>
                                                                        <p class="text-muted small mb-0">Locations or
                                                                            entities recommended for follow-up
                                                                            attention.</p>
                                                                    </div>

                                                                    <?php if (!empty($report['location_suggestions'])): ?>
                                                                    <div class="list-group list-group-flush">
                                                                        <?php foreach ((array) ($report['location_suggestions'] ?? []) as $loc): ?>
                                                                        <div class="list-group-item px-0">
                                                                            <div class="fw-semibold text-dark">
                                                                                <?= Html::encode($loc['location_name'] ?? '') ?>
                                                                            </div>
                                                                            <div class="text-muted small">
                                                                                <?= Html::encode($loc['reason'] ?? '') ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <p class="text-muted small mb-0">No surveillance
                                                                        recommendations provided.</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Methodology Tab -->
                                                <div class="tab-pane fade" id="methodology-pane-<?= $model->id ?>"
                                                    role="tabpanel" aria-labelledby="methodology-tab-<?= $model->id ?>">

                                                    <?php $analysisList = (array) ($report['analysis_basis'] ?? []); ?>
                                                    <div class="card border-0 shadow-sm rounded-4">
                                                        <div class="card-body p-4">
                                                            <div class="mb-4">
                                                                <h5 class="fw-bold mb-1">Analytical Methodology</h5>
                                                                <p class="text-muted small mb-0">Evidence, indicators,
                                                                    inference logic, and uncertainty factors.</p>
                                                            </div>

                                                            <?php if (!empty($analysisList)): ?>
                                                            <div class="accordion accordion-flush"
                                                                id="analysisBasisAccordion-<?= $model->id ?>">
                                                                <?php foreach ($analysisList as $index => $analysis): ?>
                                                                <div class="accordion-item">
                                                                    <h2 class="accordion-header"
                                                                        id="analysis-heading-<?= $model->id ?>-<?= $index ?>">
                                                                        <button
                                                                            class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>"
                                                                            type="button" data-bs-toggle="collapse"
                                                                            data-bs-target="#analysis-collapse-<?= $model->id ?>-<?= $index ?>"
                                                                            aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                                                                            aria-controls="analysis-collapse-<?= $model->id ?>-<?= $index ?>">
                                                                            Analysis Segment <?= $index + 1 ?>
                                                                        </button>
                                                                    </h2>

                                                                    <div id="analysis-collapse-<?= $model->id ?>-<?= $index ?>"
                                                                        class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                                                        aria-labelledby="analysis-heading-<?= $model->id ?>-<?= $index ?>"
                                                                        data-bs-parent="#analysisBasisAccordion-<?= $model->id ?>">
                                                                        <div class="accordion-body">

                                                                            <div class="mb-4">
                                                                                <h6
                                                                                    class="text-uppercase text-muted fw-bold small mb-2">
                                                                                    Indicators Detected</h6>
                                                                                <p class="mb-0 text-dark">
                                                                                    <?= Html::encode(
                                                                is_array($analysis['indicators_detected'] ?? null)
                                                                    ? implode(' • ', $analysis['indicators_detected'])
                                                                    : ($analysis['indicators_detected'] ?? 'Not provided')
                                                            ) ?>
                                                                                </p>
                                                                            </div>

                                                                            <div class="mb-4">
                                                                                <h6
                                                                                    class="text-uppercase text-muted fw-bold small mb-2">
                                                                                    Evidence Quotes</h6>
                                                                                <?php if (!empty($analysis['evidence_quotes'])): ?>
                                                                                <div class="list-group">
                                                                                    <?php foreach ((array) ($analysis['evidence_quotes'] ?? []) as $quote): ?>
                                                                                    <div
                                                                                        class="list-group-item rounded-3 mb-2">
                                                                                        <div class="small text-dark">
                                                                                            “<?= Html::encode($quote) ?>”
                                                                                        </div>
                                                                                    </div>
                                                                                    <?php endforeach; ?>
                                                                                </div>
                                                                                <?php else: ?>
                                                                                <p class="text-muted small mb-0">No
                                                                                    evidence quotes provided.</p>
                                                                                <?php endif; ?>
                                                                            </div>

                                                                            <div class="mb-3">
                                                                                <h6
                                                                                    class="text-uppercase text-muted fw-bold small mb-2">
                                                                                    Inference Rules Applied</h6>
                                                                                <p class="mb-0 text-dark">
                                                                                    <?= Html::encode(
                                                                is_array($analysis['inference_rules_applied'] ?? null)
                                                                    ? implode(', ', $analysis['inference_rules_applied'])
                                                                    : ($analysis['inference_rules_applied'] ?? 'Not provided')
                                                            ) ?>
                                                                                </p>
                                                                            </div>

                                                                            <?php if (!empty($analysis['uncertainty_factors'])): ?>
                                                                            <div class="alert alert-warning mb-0">
                                                                                <div class="fw-semibold mb-1">
                                                                                    <i
                                                                                        class="bi bi-exclamation-triangle me-1"></i>
                                                                                    Uncertainty Factors
                                                                                </div>
                                                                                <div class="small">
                                                                                    <?= Html::encode(
                                                                    is_array($analysis['uncertainty_factors'])
                                                                        ? implode(', ', $analysis['uncertainty_factors'])
                                                                        : $analysis['uncertainty_factors']
                                                                ) ?>
                                                                                </div>
                                                                            </div>
                                                                            <?php endif; ?>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <?php else: ?>
                                                            <p class="text-muted small mb-0">No methodology data
                                                                provided.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Interventions Tab -->
                                                <div class="tab-pane fade" id="actions-pane-<?= $model->id ?>"
                                                    role="tabpanel" aria-labelledby="actions-tab-<?= $model->id ?>">

                                                    <?php
                        $actions = $report['recommended_interventions'] ?? [];
                        if (!is_array($actions)) {
                            $actions = [$actions];
                        }
                    ?>

                                                    <div class="card border-0 shadow-sm rounded-4">
                                                        <div class="card-body p-4">
                                                            <div class="mb-4">
                                                                <h5 class="fw-bold mb-1">Recommended Interventions</h5>
                                                                <p class="text-muted small mb-0">Suggested next actions
                                                                    and responsible entities.</p>
                                                            </div>

                                                            <?php if (!empty($actions)): ?>
                                                            <div class="list-group list-group-flush">
                                                                <?php foreach ($actions as $action): ?>
                                                                <?php
                                            $entities = $action['responsible_entity'] ?? [];
                                            if (!is_array($entities)) {
                                                $entities = [$entities];
                                            }

                                            $priority = $action['priority'] ?? '';
                                            $priorityClass = ($priority === 'High')
                                                ? 'danger'
                                                : (($priority === 'Medium') ? 'warning' : 'secondary');
                                        ?>
                                                                <div class="list-group-item px-0 py-3">
                                                                    <div
                                                                        class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                                                                        <div class="flex-grow-1">
                                                                            <?php if (!empty($action['action'])): ?>
                                                                            <div class="fw-semibold text-dark mb-1">
                                                                                <?= Html::encode($action['action']) ?>
                                                                            </div>
                                                                            <?php endif; ?>

                                                                            <?php if (!empty($entities)): ?>
                                                                            <div class="text-muted small">
                                                                                Responsible:
                                                                                <span
                                                                                    class="fw-semibold"><?= Html::encode(implode(', ', $entities)) ?></span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>

                                                                        <?php if (!empty($priority)): ?>
                                                                        <div>
                                                                            <span
                                                                                class="badge bg-<?= $priorityClass ?>-subtle text-<?= $priorityClass ?> border border-<?= $priorityClass ?>">
                                                                                <?= Html::encode($priority) ?> Priority
                                                                            </span>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <?php else: ?>
                                                            <p class="text-muted small mb-0">No recommended
                                                                interventions available.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane fade intel-tab-pane" id="chats-tables-pane" role="tabpanel"
                aria-labelledby="chats-tables-tab">

                <div class="section-shell">
                    <div class="section-head">
                        <h5 class="fw-bold mb-1"><i class="bi bi-chat-square-text-fill text-primary me-2"></i>Chats</h5>
                        <small class="text-muted">Raw post conversations and evidence signals</small>
                    </div>
                    <div class="section-body">
                        <div class="row g-4">
                            <?php if (!empty($relatedPosts)): ?>
                            <?php foreach ($relatedPosts as $post): ?>
                            <?php
                                $engagement = Json::decode($post->engagement, true);
                                if (!is_array($engagement)) {
                                    $engagement = [];
                                }
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="chat-feed-card h-100 p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="platform-chip bg-primary-subtle text-primary">
                                            <?= Html::encode($post->platform) ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= !empty($post->created_at) ? date('M d, Y', strtotime($post->created_at)) : 'N/A' ?>
                                        </small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="small text-muted mb-1">Request ID</div>
                                            <div class="fw-bold"><?= Html::encode($post->request_id) ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted mb-1">Author</div>
                                            <div class="fw-bold">
                                                <span class="masked-value"
                                                    data-real="<?= Html::encode($post->author) ?>"
                                                    data-masked="<?= Html::encode(GlobalHelper::PartialMask($post->author)) ?>">
                                                    <?= Html::encode(GlobalHelper::PartialMask($post->author)) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chat-feed-text mb-3">
                                        <?= Html::encode($post->text) ?>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                        <span class="metric-chip"><i
                                                class="bi bi-hand-thumbs-up"></i><?= $engagement['likes'] ?? 0 ?></span>
                                        <span class="metric-chip"><i
                                                class="bi bi-share"></i><?= $engagement['shares'] ?? ($engagement['reposts'] ?? 0) ?></span>
                                        <span class="metric-chip"><i
                                                class="bi bi-chat"></i><?= $engagement['comments'] ?? ($engagement['replies'] ?? 0) ?></span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-auto">
                                        <button type="button" class="btn btn-primary intel-action-btn deep-decode-btn"
                                            data-post-id="<?= Html::encode($post->id) ?>"
                                            data-platform="<?= Html::encode($post->platform) ?>"
                                            data-author="<?= Html::encode($post->author) ?>"
                                            data-text="<?= Html::encode($post->text) ?>"
                                            data-source-url="<?= Html::encode($post->url ?? '') ?>"
                                            data-bs-toggle="modal" data-bs-target="#deepDecodeModal">
                                            <i class="bi bi-stars me-1"></i> Decode
                                        </button>

                                        <?php if (!empty($post->url)): ?>
                                        <a href="<?= Html::encode($post->url) ?>" target="_blank"
                                            class="btn btn-outline-dark intel-action-btn">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> View
                                        </a>
                                        <?php endif; ?>

                                        <?php $deleteForm = ActiveForm::begin([
                                            'id' => 'delete-osint-post-form-' . $post->id,
                                            'action' => ['delete-osint-post'],
                                            'options' => ['class' => 'd-inline mb-0']
                                        ]); ?>
                                        <input type="hidden" name="id" value="<?= $post->id ?>">
                                        <input type="hidden" name="request_id" value="<?= $post->request_id ?>">
                                        <button type="submit"
                                            class="btn btn-outline-danger intel-action-btn text-nowrap">
                                            <i class="bi bi-trash me-1"></i> Exclude
                                        </button>
                                        <?php ActiveForm::end(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-chat-square-x display-5 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">No chat evidence available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade intel-tab-pane" id="statistics-tables-pane" role="tabpanel"
                aria-labelledby="statistics-tables-pane">

                <?php if (isset($isCriticalView) && $isCriticalView): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Critical Threat View Enabled</h6>
                        <small>Showing only high-risk intelligence with scores of 70 or higher.</small>
                    </div>
                    <?= Html::a('Clear Filter', ['index'], ['class' => 'btn btn-sm btn-outline-danger ms-auto rounded-pill']) ?>
                </div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card intel-card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="bg-primary-subtle text-primary p-3 rounded-3">
                                        <i class="bi bi-activity fs-4"></i>
                                    </div>
                                    <span class="badge bg-light text-muted border">Mean Score</span>
                                </div>
                                <h2 class="fw-black mb-1"><?= $metrics['avgScore'] ?></h2>
                                <p class="text-muted small mb-0">Average intelligence score across all active feeds.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white"
                            onclick="window.location.href='<?= Url::to(['osint/critical']) ?>'"
                            style="cursor: pointer;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="bg-white bg-opacity-25 p-3 rounded-3">
                                        <i class="bi bi-exclamation-octagon fs-4 text-white"></i>
                                    </div>
                                    <span class="badge bg-white bg-opacity-25 border-0">Immediate Action</span>
                                </div>
                                <h2 class="fw-black mb-1"><?= $metrics['critical'] ?></h2>
                                <p class="small mb-0 opacity-75">Critical threats detected requiring immediate tactical
                                    review.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card intel-card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="bg-dark-subtle text-dark p-3 rounded-3">
                                        <i class="bi bi-rss fs-4"></i>
                                    </div>
                                    <span class="badge bg-light text-muted border">Data Ingress</span>
                                </div>
                                <h2 class="fw-black mb-1"><?= $metrics['totalPosts'] ?></h2>
                                <p class="text-muted small mb-0">Total unique social media posts analyzed in current
                                    period.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card intel-card h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="fw-bold mb-0">Platform Distribution</h5>
                                <small class="text-muted">Breakdown of intelligence sources</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="max-height: 300px;">
                                    <canvas id="platformChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <button id="toggleAllMasks" class="btn btn-outline-danger intel-action-btn float-end">
                            <i class="bi bi-eye me-1"></i> Show Usernames
                        </button>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="section-shell h-100">
                            <div class="section-head">
                                <h5 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Geospatial
                                    Hotspots</h5>
                                <small class="text-muted">Areas with highest frequency of critical signals</small>
                            </div>
                            <div class="section-body pt-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 border-0">Location</th>
                                                <th class="border-0">Alert Count</th>
                                                <th class="border-0">Max Risk</th>
                                                <th class="pe-4 border-0 text-end">Map</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($topLocations)): ?>
                                            <?php foreach ($topLocations as $name => $data): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?= Html::encode($name) ?></td>
                                                <td><span class="badge bg-dark rounded-pill"><?= $data['count'] ?>
                                                        Reports</span></td>
                                                <td>
                                                    <div class="progress" style="height: 6px; width: 100px;">
                                                        <div class="progress-bar bg-<?= $data['max_score'] >= 70 ? 'danger' : 'warning' ?>"
                                                            style="width: <?= $data['max_score'] ?>%"></div>
                                                    </div>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <a href="https://www.google.com/maps/search/<?= urlencode($name . ', Kenya') ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-light border rounded-pill">
                                                        <i class="bi bi-map"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No critical
                                                    locations detected.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="section-shell h-100">
                            <div class="section-head">
                                <h5 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Entity
                                    Mapping</h5>
                                <small class="text-muted">Users and platform activity patterns</small>
                            </div>
                            <div class="section-body pt-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 user-mapping-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 border-0">User</th>
                                                <th class="border-0">High-Threat Posts</th>
                                                <th class="border-0">Activity</th>
                                                <th class="border-0">Platforms</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($userMap)): ?>
                                            <?php foreach ($userMap as $user => $data): ?>
                                            <?php
                                                $maxCount = max(array_column($userMap, 'count'));
                                                $width = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0;
                                            ?>
                                            <tr>
                                                <td class="ps-4 fw-bold">
                                                    <span class="masked-value" data-real="<?= Html::encode($user) ?>"
                                                        data-masked="<?= Html::encode(GlobalHelper::PartialMask($user)) ?>">
                                                        <?= Html::encode(GlobalHelper::PartialMask($user)) ?>
                                                    </span>
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary rounded-pill toggle-mask ms-2 float-end">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                                <td><span
                                                        class="badge bg-danger rounded-pill"><?= $data['count'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 6px; min-width: 100px;">
                                                        <div class="progress-bar bg-danger"
                                                            style="width: <?= $width ?>%"></div>
                                                    </div>
                                                </td>
                                                <td><?= Html::encode(implode(', ', array_unique($data['platforms']))) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No entity mapping
                                                    available.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tab-pane fade intel-tab-pane" id="actions-pane" role="tabpanel" aria-labelledby="actions-tab">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <?php $form = ActiveForm::begin([
                            'id' => 'risk-score-form',
                            'options' => ['class' => 'h-100'],
                            'action' => 'manually-update-threat-score'
                        ]); ?>
                        <input type="hidden" name="request_id"
                            value="<?= Html::encode($osintaidata[0]['request_id']) ?>">

                        <div class="card intel-card overflow-hidden h-100">
                            <div id="risk-top-border" class="py-1"></div>

                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <h6 class="fw-bold text-uppercase text-secondary mb-1"
                                            style="letter-spacing: 1px;">Risk Assessment</h6>
                                        <h4 class="fw-bold text-dark mb-0">Refine Rating</h4>
                                    </div>
                                    <div class="text-end">
                                        <span id="risk-badge"
                                            class="badge rounded-pill bg-success-subtle text-success border border-success px-3 py-2">
                                            LOW RISK
                                        </span>
                                    </div>
                                </div>

                                <p class="text-muted small mb-4">
                                    Adjust the slider if the AI's calculation doesn't match the human context of this
                                    threat.
                                </p>

                                <div class="rounded-3 p-4 mb-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-white text-dark border shadow-sm px-2">Safe</span>
                                        <div class="text-center">
                                            <span id="rangescorevalue" class="display-6 fw-bold text-primary">
                                                <?= Html::encode($osintaidata[0]['numerical_score']) ?> %
                                            </span>
                                        </div>
                                        <span class="badge bg-white text-dark border shadow-sm px-2">High</span>
                                    </div>

                                    <input type="range" name="threat_score" class="form-range" min="0" max="100"
                                        value="<?= Html::encode($osintaidata[0]['numerical_score']) ?>" id="rangescore"
                                        oninput="updateRiskUI(this.value)">

                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted fw-light">Minimal Threat</small>
                                        <small class="text-muted fw-light">Critical Threat</small>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit"
                                        class="btn btn-dark intel-action-btn px-5 py-2 shadow-sm border-0">
                                        UPDATE RATING
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-dashed border-2 bg-light rounded-4 border-secondary-subtle h-100">
                            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                <div class="mb-3">
                                    <span class="fs-2 fal fa-user-clock"></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">Unsure of the current AI analysis?</h6>
                                <p class="text-muted small mb-4 px-md-5">
                                    If the source data has changed or the initial scan feels incomplete, you can trigger
                                    a
                                    <strong>Re-Analysis</strong>. The AI will perform a fresh pass on all available
                                    metadata.
                                </p>

                                <?php $form = ActiveForm::begin([
                                    'id' => 'resubmit-analysis',
                                    'options' => ['class' => 'p-0'],
                                    'action' => ['reanalyze', 'request_id' => $osintaidata[0]['request_id']]
                                ]); ?>
                                <input type="hidden" name="request_id"
                                    value="<?= Html::encode($osintaidata[0]['request_id']) ?>">

                                <div class="text-center">
                                    <button type="submit"
                                        class="btn btn-outline-primary intel-action-btn px-4 py-2 fw-semibold">
                                        <i class="fal fa-history me-1"></i> RESUBMIT FOR DEEP ANALYSIS
                                    </button>
                                </div>
                                <?php ActiveForm::end(); ?>

                                <div class="mt-3">
                                    <small class="text-muted italic" style="font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i> This process usually takes 10-15 seconds.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- FULLSCREEN DEEP DECODE MODAL -->
    <div class="modal fade fullscreen-decode-modal" id="deepDecodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header border-0 text-white"
                    style="background: linear-gradient(135deg, #0d6efd 0%, #0f172a 100%);">
                    <div>
                        <h4 class="modal-title fw-bold mb-1">
                            <i class="bi bi-cpu-fill me-2"></i> Deep Decode Workspace
                        </h4>
                        <div class="small text-white-50">
                            Cryptanalysis-assisted interpretation of an individual social signal
                        </div>
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body p-4 p-md-5">
                    <div class="container-fluid">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-8">
                                <div class="deepdecode-hero">
                                    <div class="position-relative">
                                        <div class="intel-section-label text-white-50 mb-2">Hidden Meaning Analysis
                                        </div>
                                        <h3 class="fw-bold mb-2">Analyst Decode Panel</h3>
                                        <p class="mb-3 text-white-50">
                                            This workspace converts a raw social media post into a structured
                                            intelligence
                                            interpretation.
                                        </p>

                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-white text-dark rounded-pill px-3 py-2"
                                                id="dd-platform-chip">Platform</span>
                                            <span class="badge bg-white text-dark rounded-pill px-3 py-2"
                                                id="dd-author-chip">Author</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="intel-soft-card p-4 h-100">
                                    <div class="intel-section-label mb-2">Source Context</div>
                                    <div class="small text-muted mb-3">
                                        The post below is the exact source signal being decoded.
                                    </div>
                                    <div class="decode-quote">
                                        <div class="small text-dark mb-0" id="dd-original-preview">No source selected.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="dd-loading" class="decode-loading-wrap d-none mb-4">
                            <i class="bi bi-arrow-repeat me-2"></i> Running deep decode and building structured
                            intelligence
                            output...
                        </div>

                        <div id="dd-error" class="alert alert-danger d-none"></div>

                        <div id="dd-content" class="d-none">
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div class="decode-panel">
                                        <div class="panel-head">
                                            <div class="intel-section-label">Signal Profile</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="decode-block soft mb-3">
                                                <div class="intel-section-label mb-2">Detected Language</div>
                                                <div class="fw-semibold text-dark" id="dd-language">-</div>
                                            </div>

                                            <div class="decode-block soft">
                                                <div class="intel-section-label mb-2">Communication Pattern</div>
                                                <div class="text-dark" id="dd-pattern">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="decode-panel">
                                        <div class="panel-head">
                                            <div class="intel-section-label">Plain Interpretation</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="decode-block mb-3">
                                                <div class="intel-section-label mb-2">Plain Meaning</div>
                                                <div class="text-dark" id="dd-meaning">-</div>
                                            </div>

                                            <div class="decode-block soft">
                                                <div class="intel-section-label mb-2">Hidden Signal Explanation</div>
                                                <div class="text-secondary" id="dd-explanation">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="decode-stat">
                                        <div class="intel-section-label mb-2">Risk Score</div>
                                        <div class="value text-danger" id="dd-risk">0/100</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="decode-stat">
                                        <div class="intel-section-label mb-2">Confidence</div>
                                        <div class="value text-primary" id="dd-confidence">0%</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="decode-stat">
                                        <div class="intel-section-label mb-2">Recommended Action</div>
                                        <div class="small fw-semibold text-dark" id="dd-action">-</div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="decode-panel">
                                        <div class="panel-head">
                                            <div class="intel-section-label">Indicators / Entities</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="decode-block" id="dd-entities">No entities identified.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="decode-panel">
                                        <div class="panel-head">
                                            <div class="intel-section-label">Possible Intent</div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="decode-block" id="dd-intent">-</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="decode-panel">
                                        <div class="panel-head d-flex justify-content-between align-items-center">
                                            <div class="intel-section-label">Source Link</div>
                                            <a href="#" target="_blank" id="dd-source-link"
                                                class="btn btn-outline-dark intel-action-btn btn-sm d-none">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open Source Post
                                            </a>
                                        </div>
                                        <div class="panel-body">
                                            <div class="decode-block soft">
                                                <div class="intel-section-label mb-2">Original Signal</div>
                                                <div class="text-dark" id="dd-original-full">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-white border-0">
                    <button class="btn btn-secondary intel-action-btn" data-bs-dismiss="modal">Close Decode
                        Workspace</button>
                </div>
            </div>
        </div>
    </div>

    <?php
$deepDecodeUrl = Url::to(['deep-decode-post']);

$js = <<<JS
/* =========================
   Risk Score Update Confirm
   ========================= */
$(document).on('beforeSubmit', '#risk-score-form', function(e) {
    e.preventDefault();

    let form  = $(this);
    let score = $('#rangescore').val();

    Swal.fire({
        title: 'Confirm Rating Update',
        text: 'Set score to ' + score + '%?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'YES, UPDATE RATING',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.off('beforeSubmit');
            form[0].submit();
            $(this).block({ message: 'Processing..' });
        }
    });

    return false;
});

/* =========================
   Delete OSINT Post Confirm
   ========================= */
$(document).on('beforeSubmit', '[id^="delete-osint-post-form-"]', function(e) {
    e.preventDefault();

    let form = $(this);

    Swal.fire({
        title: 'Are you sure?',
        text: 'This post will be excluded from analysis results.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'YES, EXCLUDE IT'
    }).then((result) => {
        if (result.isConfirmed) {
            form.off('beforeSubmit');
            form[0].submit();
            $(this).block({ message: 'Processing..' });
        }
    });

    return false;
});

/* =========================
   Resubmit Analysis Confirm
   ========================= */
$(document).on('beforeSubmit', '#resubmit-analysis', function(e) {
    e.preventDefault();

    let form = $(this);

    Swal.fire({
        title: 'Confirm Resubmission',
        text: 'Resubmit this data for AI Analysis?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'YES, RESUBMIT',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.off('beforeSubmit');
            form[0].submit();
            $(this).block({ message: 'Processing.' });
        }
    });

    return false;
});

function resetDeepDecodeModal() {
    $('#dd-loading').addClass('d-none');
    $('#dd-error').addClass('d-none').text('');
    $('#dd-content').addClass('d-none');

    $('#dd-platform-chip').text('Platform');
    $('#dd-author-chip').text('Author');
    $('#dd-original-preview').text('No source selected.');
    $('#dd-original-full').text('-');
    $('#dd-language').text('-');
    $('#dd-pattern').text('-');
    $('#dd-meaning').text('-');
    $('#dd-explanation').text('-');
    $('#dd-entities').text('No entities identified.');
    $('#dd-intent').text('-');
    $('#dd-risk').text('0/100');
    $('#dd-confidence').text('0%');
    $('#dd-action').text('-');
    $('#dd-source-link').addClass('d-none').attr('href', '#');
}

/* =========================
   Deep Decode Fullscreen
   ========================= */
$(document).on('click', '.deep-decode-btn', function () {
    const btn = $(this);
    const postId = btn.data('post-id');
    const text = btn.data('text');
    const platform = btn.data('platform');
    const author = btn.data('author');
    const sourceUrl = btn.data('source-url');

    resetDeepDecodeModal();

    $('#dd-platform-chip').text((platform || 'unknown').toUpperCase());
    $('#dd-author-chip').text(author || 'Unknown');
    $('#dd-original-preview').text(text || 'No source text available.');
    $('#dd-original-full').text(text || 'No source text available.');

    if (sourceUrl) {
        $('#dd-source-link').removeClass('d-none').attr('href', sourceUrl);
    }

    $('#dd-loading').removeClass('d-none');

    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Decoding...');

    $.ajax({
        url: '{$deepDecodeUrl}',
        type: 'POST',
        data: {
            post_id: postId,
            text: text,
            platform: platform,
            author: author,
            _csrf: yii.getCsrfToken()
        },
        success: function (response) {
            $('#dd-loading').addClass('d-none');

            if (response.success && response.data) {
                const data = response.data;

                $('#dd-language').text(data.detected_language || data.language || 'Unknown');
                $('#dd-pattern').text(data.communication_pattern || 'Direct / coded communication');
                $('#dd-meaning').text(data.plain_english_meaning || data.translation || 'No plain-language meaning returned.');
                $('#dd-explanation').text(data.hidden_signal_explanation || data.insights || 'No explanation available.');
                $('#dd-intent').text(data.possible_intent || 'No explicit intent returned.');
                $('#dd-risk').text((data.risk_score ?? 0) + '/100');
                $('#dd-confidence').text((data.confidence_score ?? 0) + '%');
                $('#dd-action').text(data.recommended_action || 'Continue analyst monitoring.');

                let entitiesHtml = 'No entities or indicators identified.';
                if (Array.isArray(data.entities) && data.entities.length) {
                    entitiesHtml = data.entities.map(function(item) {
                        if (typeof item === 'string') {
                            return '<span class="badge bg-light text-dark border rounded-pill px-3 py-2 me-2 mb-2">' + item + '</span>';
                        }
                        if (item && typeof item === 'object') {
                            const name = item.name || 'Unknown';
                            const type = item.type ? ' <span class="text-muted">(' + item.type + ')</span>' : '';
                            return '<div class="mb-2"><span class="fw-semibold text-dark">' + name + '</span>' + type + '</div>';
                        }
                        return '';
                    }).join('');
                }

                $('#dd-entities').html(entitiesHtml);
                $('#dd-content').removeClass('d-none');

                btn.html('<i class="bi bi-check2-circle me-1"></i> Decoded');
            } else {
                $('#dd-error').removeClass('d-none').text(response.error || 'Failed to decode this post.');
                btn.html('<i class="bi bi-stars me-1"></i> Retry Decode');
            }
        },
        error: function () {
            $('#dd-loading').addClass('d-none');
            $('#dd-error').removeClass('d-none').text('An unexpected error occurred during deep decode.');
            btn.html('<i class="bi bi-stars me-1"></i> Retry Decode');
        },
        complete: function () {
            btn.prop('disabled', false);
        }
    });
});

$('#deepDecodeModal').on('hidden.bs.modal', function () {
    resetDeepDecodeModal();
});

$(document).on('click', '#toggleAllMasks, .toggle-mask', function () {
    let button = $('#toggleAllMasks');
    let showReal = button.data('show-real') === true;

    showReal = !showReal;
    button.data('show-real', showReal);

    $('.masked-value').each(function () {
        let realVal = $(this).data('real');
        let maskedVal = $(this).data('masked');
        $(this).text(showReal ? realVal : maskedVal);
    });

    let iconClass = showReal ? 'bi bi-eye-slash' : 'bi bi-eye';
    $('.toggle-mask i, #toggleAllMasks i').attr('class', iconClass);

    button.contents().filter(function() {
        return this.nodeType === 3;
    }).remove();

    button.append(showReal ? ' Hide Usernames' : ' Show Usernames');
});
JS;

$this->registerJs($js);
?>

    <script>
    function updateRiskUI(value) {
        value = parseInt(value, 10) || 0;

        const scoreLabel = document.getElementById('rangescorevalue');
        const badge = document.getElementById('risk-badge');
        const topBorder = document.getElementById('risk-top-border');

        if (scoreLabel) {
            scoreLabel.textContent = value + ' %';
        }

        let text = 'LOW RISK';
        let badgeClass = 'badge rounded-pill bg-success-subtle text-success border border-success px-3 py-2';
        let borderColor = '#198754';

        if (value >= 70) {
            text = 'CRITICAL RISK';
            badgeClass = 'badge rounded-pill bg-danger-subtle text-danger border border-danger px-3 py-2';
            borderColor = '#dc3545';
        } else if (value >= 40) {
            text = 'ELEVATED RISK';
            badgeClass = 'badge rounded-pill bg-warning-subtle text-warning border border-warning px-3 py-2';
            borderColor = '#ffc107';
        }

        if (badge) {
            badge.className = badgeClass;
            badge.textContent = text;
        }

        if (topBorder) {
            topBorder.style.backgroundColor = borderColor;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        updateRiskUI(document.getElementById('rangescore')?.value || 0);

        const chartEl = document.getElementById('platformChart');
        if (chartEl && window.Chart) {
            const ctx = chartEl.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($metrics['platformLabels']) ?>,
                    datasets: [{
                        data: <?= json_encode($metrics['platformData']) ?>,
                        backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                            '#fd7e14'
                        ],
                        hoverOffset: 10,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.errMode = 'none';
            $('.user-mapping-table').DataTable({
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, 'All']
                ],
                dom: 'Bfrtip',
                buttons: ['excel', 'pdf'],
                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]
            });
        }
    });
    </script>