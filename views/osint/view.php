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

.fullscreen-evidence-modal .modal-content,
.fullscreen-decode-modal .modal-content {
    border: 0;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(15, 23, 42, 0.18);
}

.fullscreen-evidence-modal .modal-header,
.fullscreen-decode-modal .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.fullscreen-evidence-modal .modal-body,
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
</style>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h2 class="fw-bold tracking-tight mb-1 text-dark">
                View Post <span class="text-primary">Request ID:
                    <?= Html::encode($osintaidata[0]['request_id']) ?></span>
            </h2>
        </div>

        <div class="report-counter p-3 shadow-sm border text-center rounded-4 bg-white">
            <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1" style="font-size: 0.65rem;">
                Analyzed Reports
            </div>
            <div class="h4 m-0 fw-black text-primary"><?= count($osintaidata) ?></div>
        </div>
    </div>

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
                onclick="window.location.href='<?= Url::to(['osint/critical']) ?>'" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 p-3 rounded-3">
                            <i class="bi bi-exclamation-octagon fs-4 text-white"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-25 border-0">Immediate Action</span>
                    </div>
                    <h2 class="fw-black mb-1"><?= $metrics['critical'] ?></h2>
                    <p class="small mb-0 opacity-75">Critical threats detected requiring immediate tactical review.</p>
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
                    <p class="text-muted small mb-0">Total unique social media posts analyzed in current period.</p>
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

    <div class="row">
        <div class="col-md-12 float-end">
            <button id="toggleAllMasks" class="btn btn-outline-danger intel-action-btn mb-3 float-end">
                <i class="bi bi-eye me-1"></i> Show Usernames
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card intel-card h-100">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Geospatial Hotspots
                        </h5>
                        <small class="text-muted">Areas with highest frequency of critical signals</small>
                    </div>
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
                                    <td>
                                        <span class="badge bg-dark rounded-pill"><?= $data['count'] ?> Reports</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-<?= $data['max_score'] >= 70 ? 'danger' : 'warning' ?>"
                                                style="width: <?= $data['max_score'] ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="https://www.google.com/maps/search/<?= urlencode($name . ', Kenya') ?>"
                                            target="_blank" class="btn btn-sm btn-light border rounded-pill">
                                            <i class="bi bi-map"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No critical locations detected.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card intel-card h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Entity Mapping</h5>
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
                                    <td><span class="badge bg-danger rounded-pill"><?= $data['count'] ?></span></td>
                                    <td>
                                        <div class="progress" style="height: 6px; min-width: 100px;">
                                            <div class="progress-bar bg-danger" style="width: <?= $width ?>%"></div>
                                        </div>
                                    </td>
                                    <td><?= Html::encode(implode(', ', array_unique($data['platforms']))) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No entity mapping available.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'risk-score-form',
        'options' => ['class' => 'p-3'],
        'action' => 'manually-update-threat-score'
    ]); ?>
    <input type="hidden" name="request_id" value="<?= Html::encode($osintaidata[0]['request_id']) ?>">

    <div class="card intel-card overflow-hidden">
        <div id="risk-top-border" class="py-1"></div>

        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="fw-bold text-uppercase text-secondary mb-1" style="letter-spacing: 1px;">Risk Assessment
                    </h6>
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
                Adjust the slider if the AI's calculation doesn't match the human context of this threat.
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
                <button type="submit" class="btn btn-dark intel-action-btn px-5 py-2 shadow-sm border-0">
                    UPDATE RATING
                </button>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="row g-0 mb-4">
        <div class="col-12">
            <div class="card border-dashed border-2 bg-light rounded-4 border-secondary-subtle">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <span class="fs-2 fal fa-user-clock"></span>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Unsure of the current AI analysis?</h6>
                    <p class="text-muted small mb-4 px-md-5">
                        If the source data has changed or the initial scan feels incomplete, you can trigger a
                        <strong>Re-Analysis</strong>. The AI will perform a fresh pass on all available metadata.
                    </p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'resubmit-analysis',
                        'options' => ['class' => 'p-3'],
                        'action' => ['reanalyze', 'request_id' => $osintaidata[0]['request_id']]
                    ]); ?>
                    <input type="hidden" name="request_id" value="<?= Html::encode($osintaidata[0]['request_id']) ?>">
                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-primary intel-action-btn px-4 py-2 fw-semibold">
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
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <span class="badge rounded-pill bg-light text-dark border mb-2">ID:
                                        <?= Html::encode($model->request_id) ?></span>
                                    <h4 class="card-title fw-bold mb-1 text-uppercase">
                                        <i
                                            class="bi bi-shield-exclamation me-2"></i><?= Html::encode($model->keyword) ?>
                                    </h4>
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1"></i> Analysis Date:
                                        <?= date('M d, Y - H:i', strtotime($model->analyzed_at)) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?> px-3 py-2">
                                        STATUS: <?= $statusLabel ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-12">
                                    <div class="p-4 rounded-4 border-0 bg-white shadow-sm mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8 border-end">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div
                                                        class="bg-primary-subtle text-primary px-3 py-2 rounded-3 me-3">
                                                        <i class="fa fa-shield-alt fs-5"></i>
                                                    </div>
                                                    <h6 class="text-uppercase text-muted fw-bold small mb-0">Executive
                                                        Summary</h6>
                                                </div>
                                                <div class="text-dark">
                                                    <?= nl2br(Html::encode($report['threat_summary'] ?? 'System status nominal. No immediate threats detected.')) ?>
                                                </div>
                                            </div>

                                            <div class="col-md-4 text-center">
                                                <?php
                                                    $scoreRaw = (float) ($report['numerical_score'] ?? 0);
                                                    $displayScore = $scoreRaw <= 10 ? $scoreRaw * 10 : $scoreRaw;
                                                    $scoreColor = ($displayScore > 70) ? 'danger' : (($displayScore > 40) ? 'warning' : 'success');
                                                    ?>
                                                <h6 class="text-uppercase text-muted fw-bold small mb-3">Threat Exposure
                                                </h6>
                                                <div class="display-5 fw-bold text-<?= $scoreColor ?> mb-1">
                                                    <?= $displayScore ?>%</div>
                                                <div class="progress mt-2 mx-auto"
                                                    style="height: 8px; width: 80%; border-radius: 10px; background-color: #f0f0f0;">
                                                    <div class="progress-bar bg-<?= $scoreColor ?>" role="progressbar"
                                                        style="width: <?= $displayScore ?>%"
                                                        aria-valuenow="<?= $displayScore ?>" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <p class="small text-muted mt-2 mb-0">
                                                    Trajectory:
                                                    <strong><?= Html::encode($report['risk_trajectory'] ?? 'Stable') ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="h-100 p-4 rounded-4 border-0 bg-white shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h6 class="text-uppercase fw-bold text-muted small mb-0">Geographic Threat
                                                Vectors</h6>
                                            <button class="btn btn-sm btn-light border rounded-pill text-muted"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#map-view-<?= $model->id ?>">
                                                <i class="bi bi-map me-1"></i> View Map
                                            </button>
                                        </div>

                                        <div class="collapse mb-4" id="map-view-<?= $model->id ?>">
                                            <div class="rounded-4 overflow-hidden border position-relative"
                                                style="height:250px;">
                                                <iframe width="100%" height="100%" frameborder="0" loading="lazy"
                                                    src="<?= Html::encode($mapEmbedUrl) ?>" allowfullscreen></iframe>
                                                <a href="<?= Html::encode($mapRedirectUrl) ?>" target="_blank"
                                                    class="btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2 shadow-sm fw-bold rounded-pill">
                                                    <i class="bi bi-cursor-fill me-1"></i> Open in Maps
                                                </a>
                                            </div>
                                        </div>

                                        <?php $risks = $report['localized_risks'] ?? []; ?>
                                        <?php if (!empty($risks)): ?>
                                        <div class="vstack gap-3">
                                            <?php foreach (array_slice((array) $risks, 0, 3) as $risk): ?>
                                            <div
                                                class="p-3 rounded-3 border-start border-4 border-<?= ($risk['severity'] ?? '') === 'High' ? 'danger' : 'warning' ?> bg-light-subtle">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span
                                                        class="fw-bold text-dark"><?= Html::encode($risk['location'] ?? 'Unknown') ?></span>
                                                    <span
                                                        class="badge bg-white text-dark border-0 shadow-sm px-2 py-1 small">
                                                        <?= Html::encode($risk['severity'] ?? '') ?>
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-2">
                                                    <?= Html::encode($risk['risk_description'] ?? '') ?>
                                                </p>
                                                <?php if (!empty($risk['location'])): ?>
                                                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(($risk['location'] ?? '') . ', Kenya') ?>"
                                                    target="_blank"
                                                    class="btn btn-link p-0 text-primary text-decoration-none small">
                                                    <i class="bi bi-geo-alt"></i> Navigate to Location
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-shield-check text-success display-6"></i>
                                            <p class="text-muted mt-2">No localized risks identified.</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="h-100 p-4 rounded-4 border-0 bg-white shadow-sm">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-4">Signals & Intelligence
                                        </h6>

                                        <label class="d-block small fw-bold text-muted text-uppercase mb-2"
                                            style="letter-spacing: 1px;">Decoded Terms</label>
                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                            <?php foreach ((array) ($report['decoded_language'] ?? []) as $lang): ?>
                                            <div class="badge bg-white text-dark border fw-normal p-2 rounded-3 shadow-sm text-wrap"
                                                title="<?= Html::encode($lang['contextual_explanation'] ?? '') ?>">
                                                <span
                                                    class="text-primary fw-bold"><?= Html::encode($lang['original_term'] ?? '') ?></span>
                                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                <?= Html::encode($lang['decoded_meaning'] ?? '') ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="p-3 rounded-4 bg-primary-subtle border-0">
                                            <p class="fw-bold small text-primary mb-3">
                                                <i class="bi bi-eye-fill me-2"></i>Surveillance Protocol
                                            </p>
                                            <ul class="list-unstyled mb-0">
                                                <?php foreach ((array) ($report['location_suggestions'] ?? []) as $loc): ?>
                                                <li class="small mb-2 d-flex align-items-start">
                                                    <span class="text-primary me-2">•</span>
                                                    <div>
                                                        <span
                                                            class="fw-bold text-dark d-block"><?= Html::encode($loc['location_name'] ?? '') ?></span>
                                                        <span
                                                            class="text-muted"><?= Html::encode($loc['reason'] ?? '') ?></span>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php $analysisList = (array) ($report['analysis_basis'] ?? []); ?>
                            <?php if (!empty($analysisList)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="p-4 rounded-4 border-0 bg-dark text-white shadow-lg">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="bg-white bg-opacity-10 p-2 rounded-3 me-3">
                                                <i class="bi bi-cpu text-info fs-5"></i>
                                            </div>
                                            <h6 class="text-uppercase fw-bold small mb-0"
                                                style="letter-spacing: 1.5px;">Analytical Methodology</h6>
                                        </div>

                                        <?php foreach ($analysisList as $analysis): ?>
                                        <div class="row g-4">
                                            <div class="col-md-12 mt-2">
                                                <label
                                                    class="text-info small text-uppercase fw-bold d-block mb-2">Evidence
                                                    & Indicators</label>
                                                <p class="small text-white-50">
                                                    <?= Html::encode(is_array($analysis['indicators_detected'] ?? null) ? implode(' • ', $analysis['indicators_detected']) : ($analysis['indicators_detected'] ?? '')) ?>
                                                </p>

                                                <div
                                                    class="mt-3 bg-white bg-opacity-5 p-3 rounded-3 border-start border-info border-3">
                                                    <i class="bi bi-quote fs-4 text-info opacity-50"></i>
                                                    <ul class="list-unstyled small mb-0 italic">
                                                        <?php foreach ((array) ($analysis['evidence_quotes'] ?? []) as $quote): ?>
                                                        <li class="mb-1 text-light-emphasis">
                                                            "<?= Html::encode($quote) ?>"</li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-2">
                                                <label class="text-info small text-uppercase fw-bold d-block mb-2">Logic
                                                    & Uncertainty</label>
                                                <p class="small mb-3">
                                                    <strong>Rules:</strong>
                                                    <?= Html::encode(is_array($analysis['inference_rules_applied'] ?? null) ? implode(', ', $analysis['inference_rules_applied']) : ($analysis['inference_rules_applied'] ?? '')) ?>
                                                </p>

                                                <?php if (!empty($analysis['uncertainty_factors'])): ?>
                                                <div
                                                    class="p-2 px-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                                    <span class="small text-danger fw-bold">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>Uncertainty:
                                                    </span>
                                                    <span class="small text-white-50">
                                                        <?= Html::encode(is_array($analysis['uncertainty_factors']) ? implode(', ', $analysis['uncertainty_factors']) : $analysis['uncertainty_factors']) ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php
                                $actions = $report['recommended_interventions'] ?? [];
                                if (!is_array($actions)) {
                                    $actions = [$actions];
                                }
                                ?>
                            <?php if (!empty($actions)): ?>
                            <div class="row g-4 mb-4 mt-4">
                                <div class="col-md-12">
                                    <div class="p-4 rounded-4 border bg-white shadow-sm">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-3">Recommended
                                            Interventions</h6>

                                        <div class="list-group list-group-flush">
                                            <?php foreach ($actions as $action): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <?php if (!empty($action['action'])): ?>
                                                        <p class="small text-dark mb-1">
                                                            <?= Html::encode($action['action']) ?></p>
                                                        <?php endif; ?>

                                                        <?php
                                                                    $entities = $action['responsible_entity'] ?? [];
                                                                    if (!is_array($entities)) {
                                                                        $entities = [$entities];
                                                                    }
                                                                    ?>
                                                        <?php if (!empty($entities)): ?>
                                                        <small class="text-muted">Responsible:
                                                            <?= Html::encode(implode(', ', $entities)) ?></small>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!empty($action['priority'])): ?>
                                                    <span
                                                        class="badge rounded-pill <?= ($action['priority'] === 'High') ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                                        <?= Html::encode($action['priority']) ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-dark intel-action-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-<?= Html::encode($model->request_id) ?>">
                                        <i class="bi bi-list-ul me-1"></i> View Related Posts
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FULLSCREEN EVIDENCE MODAL -->
        <div class="modal fade fullscreen-evidence-modal" id="modal-<?= Html::encode($model->request_id) ?>"
            tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header border-0 text-white"
                        style="background: linear-gradient(135deg, #0d6efd 0%, #0f172a 100%);">
                        <div>
                            <h4 class="modal-title fw-bold mb-1">
                                <i class="bi bi-database-fill me-2"></i> Evidence Log:
                                <?= Html::encode($model->keyword) ?>
                            </h4>
                            <div class="small text-white-50">
                                Raw social signals used in the intelligence assessment
                            </div>
                        </div>
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body p-4 p-md-5">
                        <div class="container-fluid">
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-8">
                                    <div class="deepdecode-hero">
                                        <div class="position-relative">
                                            <div class="intel-section-label text-white-50 mb-2">Evidence Workspace</div>
                                            <h3 class="fw-bold mb-2">Raw Social Signals</h3>
                                            <p class="mb-0 text-white-50">
                                                Review the exact posts behind this intelligence score, then launch
                                                <strong class="text-white">Deep Decode</strong> to interpret coded
                                                language,
                                                hidden meaning, or suspicious phrasing.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mt-3 mt-lg-0">
                                    <div class="intel-soft-card p-4 h-100">
                                        <div class="intel-section-label mb-2">Quick Summary</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="metric-chip"><i class="bi bi-hash"></i>
                                                <?= Html::encode($model->request_id) ?></span>
                                            <span class="metric-chip"><i class="bi bi-exclamation-triangle"></i>
                                                <?= Html::encode($statusLabel) ?></span>
                                            <span class="metric-chip"><i class="bi bi-bar-chart"></i> Score
                                                <?= (int)$score ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <?php
                                    $foundPosts = false;
                                    foreach ($relatedPosts as $post):
                                        if ($post->request_id === $model->request_id):
                                            $foundPosts = true;
                                            $engagement = Json::decode($post->engagement, true);
                                            if (!is_array($engagement)) {
                                                $engagement = [];
                                            }
                                            ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="evidence-post-card h-100 p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="platform-chip bg-primary-subtle text-primary">
                                                <?= Html::encode($post->platform) ?>
                                            </span>
                                            <small class="text-muted">
                                                <?= !empty($post->created_at) ? date('M d, Y', strtotime($post->created_at)) : 'N/A' ?>
                                            </small>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="fw-bold mb-0 pe-2">
                                                <span class="masked-value"
                                                    data-real="<?= Html::encode($post->author) ?>"
                                                    data-masked="<?= Html::encode(GlobalHelper::PartialMask($post->author)) ?>">
                                                    <?= Html::encode(GlobalHelper::PartialMask($post->author)) ?>
                                                </span>
                                            </h6>

                                            <button class="btn btn-sm btn-outline-secondary rounded-pill toggle-mask">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                        <div class="decode-quote mb-3">
                                            <p class="card-text text-secondary small mb-0">
                                                “<?= Html::encode($post->text) ?>”
                                            </p>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                            <span class="metric-chip">
                                                <i class="bi bi-hand-thumbs-up"></i><?= $engagement['likes'] ?? 0 ?>
                                            </span>
                                            <span class="metric-chip">
                                                <i
                                                    class="bi bi-share"></i><?= $engagement['shares'] ?? ($engagement['reposts'] ?? 0) ?>
                                            </span>
                                            <span class="metric-chip">
                                                <i
                                                    class="bi bi-chat"></i><?= $engagement['comments'] ?? ($engagement['replies'] ?? 0) ?>
                                            </span>
                                            <span class="metric-chip ms-auto">
                                                <i class="bi bi-body-text"></i> Text Signal
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 mt-3 flex-nowrap overflow-auto">
                                            <button type="button" class="btn btn-primary deep-decode-btn"
                                                data-post-id="<?= Html::encode($post->id) ?>"
                                                data-platform="<?= Html::encode($post->platform) ?>"
                                                data-author="<?= Html::encode($post->author) ?>"
                                                data-text="<?= Html::encode($post->text) ?>"
                                                data-source-url="<?= Html::encode($post->url ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#deepDecodeModal">
                                                <i class="bi bi-stars me-1"></i> Deep Decode
                                            </button>

                                            <?php if (!empty($post->url)): ?>
                                            <a href="<?= Html::encode($post->url) ?>" target="_blank"
                                                class="btn btn-outline-dark">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open Source
                                            </a>
                                            <?php endif; ?>

                                            <?php $deleteForm = ActiveForm::begin([
        'id' => 'delete-osint-post-form-' . $post->id,
        'action' => ['exclude-post', 'id' => $post->id],
        'options' => ['class' => 'd-inline mb-0']
    ]); ?>
                                            <button type="submit" class="btn btn-outline-danger text-nowrap">
                                                <i class="bi bi-trash me-1"></i> Exclude From Analysis
                                            </button>
                                            <?php ActiveForm::end(); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if (!$foundPosts): ?>
                                <div class="col-12 text-center py-5">
                                    <i class="bi bi-folder-x display-1 text-muted"></i>
                                    <p class="mt-3">No specific social posts linked to this Analysis ID.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0">
                        <button class="btn btn-secondary intel-action-btn" data-bs-dismiss="modal">Close Evidence
                            Workspace</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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
                                    <div class="intel-section-label text-white-50 mb-2">Hidden Meaning Analysis</div>
                                    <h3 class="fw-bold mb-2">Analyst Decode Panel</h3>
                                    <p class="mb-3 text-white-50">
                                        This workspace converts a raw social media post into a structured intelligence
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
                                    <div class="small text-dark mb-0" id="dd-original-preview">No source selected.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="dd-loading" class="decode-loading-wrap d-none mb-4">
                        <i class="bi bi-arrow-repeat me-2"></i> Running deep decode and building structured intelligence
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