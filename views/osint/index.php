<?php

use app\helpers\GlobalHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'OSINT Intelligence Feed';
$relatedCount = 0;

$headlineLocation = !empty($topLocations) ? array_key_first($topLocations) : 'Kenya';
$headlinePlatform = !empty($metrics['platformLabels'][0]) ? $metrics['platformLabels'][0] : 'multi-platform sources';
$headlineCritical = (int)($metrics['critical'] ?? 0);
$headlineAvg = (int)($metrics['avgScore'] ?? 0);
?>

<style>
.btn-check:checked+.btn-outline-primary {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.5);
    border-color: #0d6efd;
}

.bg-glass {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.transition-focus:focus-within {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.btn-white-to-primary {
    background: white;
    color: #6c757d;
    transition: all 0.3s ease;
}

.btn-check:checked+.btn-white-to-primary {
    background-color: #0d6efd;
    color: white;
}

.hover-grow {
    transition: transform 0.2s ease;
}

.hover-grow:hover {
    transform: scale(1.02);
}

.form-range::-webkit-slider-thumb,
.form-range::-moz-range-thumb {
    background: #0d6efd;
}

.model-card {
    cursor: pointer;
    border-radius: 16px;
    transition: all 0.25s ease;
    background: #ffffff;
}

.model-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.btn-check:checked+.model-card {
    border: 2px solid #0d6efd;
    background: rgba(13, 110, 253, 0.05);
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.report-counter {
    border-radius: 18px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
}

.intel-banner {
    border-radius: 22px;
    background: linear-gradient(135deg, #0d6efd 0%, #081f4d 100%);
    color: #fff;
    overflow: hidden;
    position: relative;
}

.intel-banner::after {
    content: "";
    position: absolute;
    top: -30px;
    right: -20px;
    width: 180px;
    height: 180px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.mission-chip {
    border-radius: 999px;
    font-size: 0.8rem;
    padding: 0.55rem 1rem;
}

.workflow-step {
    border-radius: 16px;
    background: #fff;
    border: 1px solid rgba(13, 110, 253, 0.08);
    padding: 1rem;
    height: 100%;
}

.workflow-step .icon-wrap {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(13, 110, 253, 0.08);
    color: #0d6efd;
}

.actor-card-title,
.section-kicker {
    letter-spacing: 1px;
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #6c757d;
}

.analysis-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    border: 1px solid #dee2e6;
    background: #fff;
    font-size: 0.82rem;
}

.executive-headline {
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(220,53,69,0.08) 100%);
    border: 1px solid rgba(13,110,253,0.12);
}

.primary-hotspot-card {
    border-radius: 18px;
    background: linear-gradient(135deg, #fff6f6 0%, #ffffff 100%);
    border: 1px solid rgba(220,53,69,0.12);
}

.intel-loading-panel {
    max-width: 760px;
    margin: 0 auto;
    border-radius: 24px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
}

.scan-stage {
    border-radius: 16px;
    padding: 0.85rem 1rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.scan-stage.active {
    background: rgba(13,110,253,0.08);
    border-color: rgba(13,110,253,0.18);
    color: #0d6efd;
}

.privacy-note {
    border-radius: 16px;
    background: #fff8e1;
    border: 1px solid #ffe69c;
}

.rotate-text {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    letter-spacing: 2px;
}

.x-small {
    font-size: 0.75rem;
}

.metric-card {
    min-height: 170px;
}

.lead-tight {
    line-height: 1.7;
}

.evidence-quote {
    border-left: 3px solid #dee2e6;
    padding-left: 0.85rem;
}

@media (max-width: 767.98px) {
    .rotate-text {
        writing-mode: initial;
        transform: none;
        letter-spacing: 1px;
    }
}
</style>

<div class="container- py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h2 class="fw-bold tracking-tight mb-1 text-dark">
                Social Media Threat Intelligence <span class="text-primary">Dashboard</span>
            </h2>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <small class="text-muted fw-medium">
                    <i class="bi bi-cpu-fill me-1"></i> Multi-platform Analysis:
                </small>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">X</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">Facebook</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">TikTok</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">Reddit</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">Google News</span>
            </div>
        </div>

        <div class="report-counter p-3 shadow-sm border text-center">
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

    <div class="intel-banner shadow-lg p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <div class="section-kicker text-white-50 mb-2">Today’s Intelligence Signal</div>
                <h3 class="fw-bold mb-2">
                    Elevated monitoring focus around <?= Html::encode($headlineLocation) ?>
                </h3>
                <p class="mb-3 text-white-50">
                    Multi-platform signals are being fused into analyst-ready briefings, with strongest dashboard pressure
                    currently visible from <?= Html::encode($headlinePlatform) ?> and an average threat score of
                    <?= $headlineAvg ?>.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white text-dark mission-chip">
                        <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                        Critical alerts: <?= $headlineCritical ?>
                    </span>
                    <span class="badge bg-white text-dark mission-chip">
                        <i class="bi bi-diagram-3-fill text-primary me-1"></i>
                        Cross-platform corroboration
                    </span>
                    <span class="badge bg-white text-dark mission-chip">
                        <i class="bi bi-shield-lock-fill text-warning me-1"></i>
                        Privacy-aware analyst mode
                    </span>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="bg-white bg-opacity-10 rounded-4 p-4">
                    <div class="small text-uppercase fw-bold text-white-50 mb-2">Operational Workflow</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="workflow-step bg-transparent border border-light border-opacity-25 text-white">
                                <div class="icon-wrap bg-white bg-opacity-10 text-white mb-2">
                                    <i class="bi bi-search"></i>
                                </div>
                                <div class="small fw-semibold">Collect Signals</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="workflow-step bg-transparent border border-light border-opacity-25 text-white">
                                <div class="icon-wrap bg-white bg-opacity-10 text-white mb-2">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <div class="small fw-semibold">Correlate Narratives</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="workflow-step bg-transparent border border-light border-opacity-25 text-white">
                                <div class="icon-wrap bg-white bg-opacity-10 text-white mb-2">
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <div class="small fw-semibold">AI Risk Assessment</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="workflow-step bg-transparent border border-light border-opacity-25 text-white">
                                <div class="icon-wrap bg-white bg-opacity-10 text-white mb-2">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="small fw-semibold">Actionable Brief</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 metric-card">
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
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white metric-card"
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
            <div class="card border-0 shadow-sm rounded-4 h-100 metric-card">
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

    <div class="row g-4 mb-5">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
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
        <div class="col-md-12">
            <button id="toggleAllMasks" class="btn btn-sm btn-outline-danger mb-3 float-end">
                <i class="bi bi-eye"></i> Show Usernames
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Geospatial Hotspots</h5>
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
                                                <span class="badge bg-dark rounded-pill">
                                                    <?= $data['count'] ?> Reports
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar bg-<?= $data['max_score'] >= 70 ? 'danger' : 'warning' ?>"
                                                         style="width: <?= $data['max_score'] ?>%">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="https://www.google.com/maps/search/<?= urlencode($name . ', Kenya') ?>"
                                                   target="_blank" class="btn btn-sm btn-light border">
                                                    <i class="bi bi-map"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No critical locations detected.
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
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="actor-card-title">Actor Prioritization</div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-people-fill text-primary me-2"></i>High-Risk Actors
                            </h5>
                            <small class="text-muted">
                                Recurrent accounts or entities appearing in high-threat narratives across monitored platforms.
                            </small>
                        </div>
                        <span class="badge bg-warning-subtle text-dark border">Masked by default</span>
                    </div>

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
                                    <?php
                                    $maxCount = max(array_column($userMap, 'count'));
                                    ?>
                                    <?php foreach ($userMap as $user => $data): ?>
                                        <?php $width = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0; ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">
                                                <span class="masked-value"
                                                      data-real="<?= Html::encode($user) ?>"
                                                      data-masked="<?= Html::encode(GlobalHelper::PartialMask($user)) ?>">
                                                    <?= Html::encode(GlobalHelper::PartialMask($user)) ?>
                                                </span>

                                                <button class="btn btn-sm toggle-mask ms-2 float-end">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger rounded-pill"><?= $data['count'] ?></span>
                                            </td>
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
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No actor prioritization data available.
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

    <hr class="opacity-10 mb-4">

    <div class="container- py-4">
        <div class="mb-4">
            <label class="text-uppercase fw-bold text-secondary mb-3 d-block" style="font-size: 0.75rem; letter-spacing: 1.5px;">
                <i class="bi bi-broadcast-pin me-1"></i> Threat Scenarios
            </label>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium"
                        data-keyword="al shabaab terrorism attack Kenya">
                    <i class="bi bi-shield-lock-fill me-1"></i> Terrorism
                </button>

                <button class="btn btn-outline-warning btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium text-dark"
                        data-keyword="kidnapped abducted ransom Kenya">
                    <i class="bi bi-person-exclamation me-1"></i> Kidnapping / Abduction
                </button>

                <button class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium"
                        data-keyword="gang violence shooting Kenya">
                    <i class="bi bi-geo-alt-fill me-1"></i> Gang Violence
                </button>

                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium"
                        data-keyword="protest unrest demonstrations violence Kenya">
                    <i class="bi bi-megaphone-fill me-1"></i> Civil Unrest
                </button>

                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium"
                        data-keyword="disinformation incitement propaganda Kenya">
                    <i class="bi bi-chat-left-text-fill me-1"></i> Disinformation
                </button>

                <button class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 shadow-sm fw-medium"
                        data-keyword="pipeline sabotage infrastructure attack blackout Kenya">
                    <i class="bi bi-building-lock me-1"></i> Infrastructure Threat
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-lg p-4 rounded-4 bg-glass">
            <form id="osint-search-form" class="w-100">
                <div class="row g-4">
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase ps-2">Target Narrative / Keyword</label>
                            <div class="bg-light rounded-pill border p-1 d-flex align-items-center transition-focus">
                                <div class="ps-4 text-primary">
                                    <i class="bi bi-search fs-5"></i>
                                </div>

                                <input id="keyword-input" name="keyword" type="text"
                                       class="form-control border-0 bg-transparent py-3 shadow-none flex-grow-1"
                                       placeholder="Search threat narratives, coded terms, incidents, people, or locations..."
                                       style="font-size: 1.05rem;">

                                <button class="btn btn-primary rounded-pill px-5 py-2 me-1 fw-bold text-uppercase shadow-sm hover-grow"
                                        type="submit" id="submit-scan">
                                    Run Scan <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="privacy-note p-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-incognito fs-5 text-warning"></i>
                                <div>
                                    <div class="fw-semibold small">Privacy-aware analyst mode</div>
                                    <div class="small text-muted">
                                        Usernames remain masked by default in dashboard views and can be revealed only when needed for review.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-md-3">
                                <div class="workflow-step">
                                    <div class="icon-wrap mb-2"><i class="bi bi-collection"></i></div>
                                    <div class="small fw-semibold">Collect</div>
                                    <div class="x-small text-muted">X, Facebook, TikTok, Reddit, Google News</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step">
                                    <div class="icon-wrap mb-2"><i class="bi bi-shuffle"></i></div>
                                    <div class="small fw-semibold">Fuse</div>
                                    <div class="x-small text-muted">Cross-platform signals</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step">
                                    <div class="icon-wrap mb-2"><i class="bi bi-braces-asterisk"></i></div>
                                    <div class="small fw-semibold">Decode</div>
                                    <div class="x-small text-muted">Sheng, dog whistles, coded cues</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step">
                                    <div class="icon-wrap mb-2"><i class="bi bi-clipboard-data"></i></div>
                                    <div class="small fw-semibold">Brief</div>
                                    <div class="x-small text-muted">Action-ready intelligence output</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-3 d-block">
                                Analysis Engine
                            </label>

                            <div class="row g-3 mb-2">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="ai_model" id="model-mini" value="gpt-4o-mini">
                                    <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-mini">
                                        <div class="d-flex justify-content-between mb-2">
                                            <i class="bi bi-lightning-charge text-warning fs-4"></i>
                                            <span class="badge bg-light text-muted">Fast</span>
                                        </div>
                                        <h6 class="fw-bold mb-1">GPT-4o Mini</h6>
                                        <small class="text-muted d-block mb-2">Rapid ingestion, filtering, and lightweight classification</small>
                                        <div class="small text-muted">Best for fast demos and broad signal triage</div>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="ai_model" id="model-4o" value="gpt-4o" checked>
                                    <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-4o">
                                        <div class="d-flex justify-content-between mb-2">
                                            <i class="bi bi-cpu text-primary fs-4"></i>
                                            <span class="badge bg-primary-subtle text-primary">Recommended</span>
                                        </div>
                                        <h6 class="fw-bold mb-1">GPT-4o</h6>
                                        <small class="text-muted d-block mb-2">Balanced intelligence for field-ready OSINT assessment</small>
                                        <div class="small text-muted">Best overall choice for reasoning, cost and speed</div>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="ai_model" id="model-thinking" value="gpt-5-thinking">
                                    <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-thinking">
                                        <div class="d-flex justify-content-between mb-2">
                                            <i class="bi bi-brain text-danger fs-4"></i>
                                            <span class="badge bg-light text-muted">Advanced</span>
                                        </div>
                                        <h6 class="fw-bold mb-1">GPT-5 Thinking</h6>
                                        <small class="text-muted d-block mb-2">Deep reasoning for sensitive or high-risk narratives</small>
                                        <div class="small text-muted">Best for complex correlations and richer threat explanations</div>
                                    </label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4"
                                        data-bs-toggle="collapse" data-bs-target="#more-models">
                                    <i class="bi bi-grid me-1"></i> More OpenAI Models
                                </button>
                            </div>

                            <div class="collapse mt-3" id="more-models">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="ai_model" id="model-nano" value="gpt-5-nano">
                                        <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-nano">
                                            <i class="bi bi-lightning text-warning fs-4 mb-2"></i>
                                            <h6 class="fw-bold mb-1">GPT-5 Nano</h6>
                                            <small class="text-muted">Ultra-lightweight & cheapest processing</small>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="ai_model" id="model-balanced-mini" value="gpt-5-mini">
                                        <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-balanced-mini">
                                            <i class="bi bi-sliders text-primary fs-4 mb-2"></i>
                                            <h6 class="fw-bold mb-1">GPT-5 Mini</h6>
                                            <small class="text-muted">Balanced speed + reasoning</small>
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="ai_model" id="model-gpt5" value="gpt-5">
                                        <label class="card model-card h-100 p-3 border-0 shadow-sm" for="model-gpt5">
                                            <i class="bi bi-stars text-danger fs-4 mb-2"></i>
                                            <h6 class="fw-bold mb-1">GPT-5</h6>
                                            <small class="text-muted">Maximum intelligence & reasoning power</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-0">
                                Analysis Depth: <span class="text-primary" id="token-display">2,000</span>
                            </label>
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle"
                                  id="cost-display">Est: < $0.01</span>
                        </div>

                        <div class="p-3 bg-light rounded-4 border">
                            <input type="range" class="form-range" id="max_tokens" name="max_tokens" min="200" max="5000" step="200" value="2000">
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                                <span id="length-label">Standard Report</span>
                                <i class="bi bi-info-circle"
                                   title="Higher tokens produce deeper analysis, longer summaries, and richer evidence linkage."></i>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="loading-spinner" class="py-5 d-none">
        <div class="intel-loading-panel border shadow-sm p-4 p-md-5 text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5 class="fw-bold mb-2">Generating Intelligence Brief</h5>
            <p class="text-muted mb-4">
                Collecting multi-platform signals, correlating narratives, decoding threat language, and producing an analyst-ready brief.
            </p>

            <div class="row g-3 text-start" id="scan-workflow">
                <div class="col-md-6">
                    <div class="scan-stage active" data-stage="1">
                        <i class="bi bi-search me-2"></i> Connecting to monitored platforms
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="scan-stage" data-stage="2">
                        <i class="bi bi-diagram-3 me-2"></i> Correlating repeated narratives
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="scan-stage" data-stage="3">
                        <i class="bi bi-braces-asterisk me-2"></i> Decoding language and dog whistles
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="scan-stage" data-stage="4">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i> Producing intelligence assessment
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

            $score = (int)$model->numerical_score;
            $statusColor = ($score >= 70) ? 'danger' : (($score >= 40) ? 'warning' : 'success');
            $statusLabel = ($score >= 70) ? 'CRITICAL' : (($score >= 40) ? 'ELEVATED' : 'STABLE');

            $localizedRisks = $report['localized_risks'] ?? [];
            if (!is_array($localizedRisks)) {
                $localizedRisks = [$localizedRisks];
            }

            $primaryRisk = !empty($localizedRisks[0]) && is_array($localizedRisks[0]) ? $localizedRisks[0] : [];
            $primaryLoc = $primaryRisk['location'] ?? 'Kenya';
            $primaryReasonLabel = $primaryRisk['risk_description'] ?? 'No geographic concentration explicitly stated.';
            $primaryLocationLabel = $primaryLoc;

            $trajectory = $report['risk_trajectory'] ?? 'Stable';

            $analysisBasis = $report['analysis_basis'] ?? [];
            if (!is_array($analysisBasis)) {
                $analysisBasis = [$analysisBasis];
            }

            $interventions = $report['recommended_interventions'] ?? [];
            if (!is_array($interventions)) {
                $interventions = [$interventions];
            }

            if ($score >= 80) {
                $actionLabel = 'Immediate Review';
            } elseif ($score >= 60) {
                $actionLabel = 'Escalate';
            } elseif ($score >= 30) {
                $actionLabel = 'Monitor Closely';
            } else {
                $actionLabel = 'Routine Monitoring';
            }

            $mapEmbedUrl = "https://www.google.com/maps?q=" . urlencode($primaryLoc . ", Kenya") . "&output=embed";
            $mapRedirectUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($primaryLoc . ", Kenya");
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden rounded-4 mb-4">
                    <div class="row g-0">
                        <div class="col-md-1 bg-<?= $statusColor ?> d-flex flex-column justify-content-center align-items-center text-white py-4">
                            <small class="fw-bold opacity-75 rotate-text mb-2">SCORE</small>
                            <h2 class="fw-black mb-0"><?= $score ?></h2>
                        </div>

                        <div class="col-md-11">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <span class="badge rounded-pill bg-light text-dark border mb-2">ID: <?= Html::encode($model->request_id) ?></span>
                                        <h4 class="card-title fw-bold mb-1 text-uppercase">
                                            <i class="bi bi-shield-exclamation me-2"></i>
                                            <a href="<?= Url::to(['view', 'request_id' => $model->request_id]) ?>"
                                                class="text-link text-decoration-none"><?= Html::encode($model->keyword) ?></a>
                                        </h4>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock me-1"></i> Analysis Date:
                                            <?= date('M d, Y - H:i', strtotime($model->analyzed_at)) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?> px-3 py-2">
                                            Threat Level: <?= $statusLabel ?>
                                        </span>
                                        <div class="small text-muted mt-2">
                                            Recommended Action: <strong><?= Html::encode($actionLabel) ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="executive-headline p-4 mb-4">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-8">
                                            <div class="section-kicker mb-2">Headline Intelligence Signal</div>
                                            <h5 class="fw-bold mb-2">
                                                Monitoring focus on <?= Html::encode($primaryLocationLabel) ?>
                                            </h5>
                                            <p class="mb-0 text-muted">
                                                <?= Html::encode($primaryReasonLabel) ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                                <span class="analysis-pill">
                                                    <i class="bi bi-graph-up-arrow text-danger"></i>
                                                    <?= Html::encode($trajectory) ?>
                                                </span>
                                                <span class="analysis-pill">
                                                    <i class="bi bi-speedometer2 text-primary"></i>
                                                    Score <?= $score ?>/100
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-12">
                                        <div class="p-4 rounded-4 border bg-white shadow-lg">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                                                        <i class="fa fa-file fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <h6 class="text-uppercase text-muted fw-bold small mb-1">Executive Summary</h6>
                                                    <div class="lead fs-6 text-dark lead-tight">
                                                        <?= nl2br(Html::encode($report['threat_summary'] ?? 'Clean - No threats detected.')) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($analysisBasis)): ?>
                                        <div class="col-md-12">
                                            <div class="p-4 rounded-4 border bg-light-subtle">
                                                <div class="section-kicker mb-3">Why this was flagged</div>
                                                <div class="row g-3">
                                                    <?php foreach (array_slice($analysisBasis, 0, 3) as $basis): ?>
                                                        <?php if (!is_array($basis)) continue; ?>
                                                        <div class="col-md-12">
                                                            <div class="bg-white border rounded-4 p-3 h-100">
                                                                <div class="fw-semibold small mb-2 text-dark">
                                                                    <?= Html::encode(
                                                                        is_array($basis['indicators_detected'] ?? null)
                                                                            ? implode(', ', $basis['indicators_detected'])
                                                                            : ($basis['indicators_detected'] ?? 'Threat indicators')
                                                                    ) ?>
                                                                </div>
                                                                <div class="small text-muted mb-2">
                                                                    <?= Html::encode(
                                                                        is_array($basis['inference_rules_applied'] ?? null)
                                                                            ? implode(', ', $basis['inference_rules_applied'])
                                                                            : ($basis['inference_rules_applied'] ?? 'Pattern-based analytical inference')
                                                                    ) ?>
                                                                </div>
                                                                <?php if (!empty($basis['uncertainty_factors'])): ?>
                                                                    <div class="small text-warning">
                                                                        <strong>Uncertainty:</strong>
                                                                        <?= Html::encode(
                                                                            is_array($basis['uncertainty_factors'])
                                                                                ? implode(', ', $basis['uncertainty_factors'])
                                                                                : $basis['uncertainty_factors']
                                                                        ) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-md-12 border-end">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <div class="section-kicker mb-1">Primary Area of Concern</div>
                                                <h6 class="text-uppercase fw-bold text-muted small mb-0">
                                                    Geographic Threat Vectors
                                                </h6>
                                            </div>

                                            <button class="btn btn-sm btn-outline-primary border-0" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#map-<?= $model->id ?>">
                                                <i class="bi bi-geo-alt"></i> Toggle Map
                                            </button>
                                        </div>

                                        <div class="primary-hotspot-card p-3 mb-3">
                                            <div class="small text-uppercase text-muted fw-bold mb-1">Primary hotspot</div>
                                            <div class="fw-bold text-dark"><?= Html::encode($primaryLocationLabel) ?></div>
                                            <div class="small text-muted"><?= Html::encode($primaryReasonLabel) ?></div>
                                        </div>

                                        <div class="collapse mb-3" id="map-<?= $model->id ?>">
                                            <div class="rounded-3 overflow-hidden border shadow-sm position-relative" style="height:220px;">
                                                <iframe width="100%" height="100%" frameborder="0" loading="lazy"
                                                        src="<?= Html::encode($mapEmbedUrl) ?>" allowfullscreen></iframe>

                                                <a href="<?= Html::encode($mapRedirectUrl) ?>" target="_blank"
                                                   class="btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2 shadow-sm fw-bold">
                                                    <i class="bi bi-cursor-fill me-1"></i> Open in Maps
                                                </a>
                                            </div>
                                        </div>

                                        <?php if (!empty($localizedRisks)): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach (array_slice($localizedRisks, 0, 3) as $risk): ?>
                                                    <?php if (!is_array($risk)) continue; ?>
                                                    <div class="list-group-item px-0 border-0 bg-transparent mb-2">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="text-dark small">
                                                                <?= Html::encode($risk['location'] ?? '') ?>
                                                            </strong>

                                                            <?php if (!empty($risk['severity'])): ?>
                                                                <span class="badge rounded-pill bg-light text-<?= ($risk['severity'] === 'High' || $risk['severity'] === 'Critical') ? 'danger' : 'warning' ?> border small">
                                                                    <?= Html::encode($risk['severity']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (!empty($risk['risk_description'])): ?>
                                                            <p class="text-muted small mb-1">
                                                                <?= Html::encode($risk['risk_description']) ?>
                                                            </p>
                                                        <?php endif; ?>

                                                        <?php if (!empty($risk['location'])): ?>
                                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(($risk['location'] ?? '') . ', Kenya') ?>"
                                                               target="_blank" class="text-primary x-small text-decoration-none">
                                                                <i class="bi bi-arrow-up-right-circle me-1"></i>
                                                                Open to Map
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-light py-2 small">
                                                No specific geographic risks identified.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-12">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-3">
                                            Signals & Intelligence
                                        </h6>

                                        <?php
                                        $decoded = $report['decoded_language'] ?? [];
                                        if (!is_array($decoded)) {
                                            $decoded = [$decoded];
                                        }
                                        ?>
                                        <?php if (!empty($decoded)): ?>
                                            <div class="bg-white border rounded-4 p-3 mb-3">
                                                <div class="section-kicker mb-2">Decoded Language</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php foreach ($decoded as $lang): ?>
                                                        <?php if (!is_array($lang)) continue; ?>
                                                        <span class="badge bg-white text-dark border p-2 fw-normal"
                                                              title="<?= Html::encode($lang['contextual_explanation'] ?? '') ?>">
                                                            <span class="text-primary fw-bold">
                                                                <?= Html::encode($lang['original_term'] ?? '') ?>:
                                                            </span>
                                                            <?= Html::encode($lang['decoded_meaning'] ?? '') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $dogWhistles = $report['dog_whistles'] ?? [];
                                        if (!is_array($dogWhistles)) {
                                            $dogWhistles = [$dogWhistles];
                                        }
                                        ?>
                                        <?php if (!empty($dogWhistles)): ?>
                                            <div class="bg-white border rounded-4 p-3 mb-3">
                                                <div class="section-kicker mb-2">Dog Whistles</div>
                                                <ul class="small text-muted mb-0">
                                                    <?php foreach ($dogWhistles as $dw): ?>
                                                        <li class="mb-1">
                                                            <?php
                                                            if (is_array($dw)) {
                                                                $phrase = $dw['phrase'] ?? '';
                                                                $signal = $dw['implied_signal'] ?? '';
                                                                $type = $dw['threat_type'] ?? '';
                                                                echo Html::encode(trim($phrase . ' - ' . $signal . ($type ? ' (' . $type . ')' : '')));
                                                            } else {
                                                                echo Html::encode((string)$dw);
                                                            }
                                                            ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $locations = $report['location_suggestions'] ?? [];
                                        if (!is_array($locations)) {
                                            $locations = [$locations];
                                        }
                                        ?>
                                        <?php if (!empty($locations)): ?>
                                            <div class="bg-light rounded p-3 mb-3">
                                                <div class="section-kicker mb-2">Surveillance Recommendations</div>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($locations as $loc): ?>
                                                        <?php if (!is_array($loc)) continue; ?>
                                                        <li class="small text-muted mb-1">
                                                            • <span class="text-dark fw-medium">
                                                                <?= Html::encode($loc['location_name'] ?? '') ?>
                                                            </span> :
                                                            <?= Html::encode($loc['reason'] ?? '') ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($interventions)): ?>
                                            <div class="bg-white border rounded-4 p-3 mb-3">
                                                <div class="section-kicker mb-2">Recommended Interventions</div>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach (array_slice($interventions, 0, 3) as $item): ?>
                                                        <?php if (!is_array($item)) continue; ?>
                                                        <li class="small text-muted mb-2">
                                                            <span class="fw-semibold text-dark">
                                                                <?= Html::encode($item['action'] ?? 'Operational response') ?>
                                                            </span>
                                                            <?php if (!empty($item['priority'])): ?>
                                                                <span class="badge bg-light text-dark border ms-1"><?= Html::encode($item['priority']) ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['responsible_entity'])): ?>
                                                                <div class="x-small text-secondary">
                                                                    Responsible: <?= Html::encode($item['responsible_entity']) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($report['risk_trajectory']) || $score > 0): ?>
                                            <div class="bg-light rounded p-3">
                                                <?php if (!empty($report['risk_trajectory'])): ?>
                                                    <p class="small mb-1">
                                                        <strong>Risk Trajectory:</strong>
                                                        <?= Html::encode($report['risk_trajectory']) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <p class="small mb-0">
                                                    <strong>Threat Score:</strong>
                                                    <?= $score ?>/100
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($analysisBasis)): ?>
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-12">
                                            <div class="p-4 rounded-4 border bg-white shadow-sm">
                                                <h6 class="text-uppercase fw-bold text-muted small mb-3">
                                                    Analyst Assessment
                                                </h6>

                                                <?php foreach ($analysisBasis as $analysis): ?>
                                                    <?php if (!is_array($analysis)) continue; ?>

                                                    <?php if (!empty($analysis['indicators_detected'])): ?>
                                                        <p class="small mb-2">
                                                            <strong>Indicators Detected:</strong>
                                                            <?= Html::encode(
                                                                is_array($analysis['indicators_detected'])
                                                                    ? implode(', ', $analysis['indicators_detected'])
                                                                    : $analysis['indicators_detected']
                                                            ) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php
                                                    $quotes = $analysis['evidence_quotes'] ?? [];
                                                    if (!is_array($quotes)) {
                                                        $quotes = [$quotes];
                                                    }
                                                    ?>
                                                    <?php if (!empty($quotes)): ?>
                                                        <div class="mb-2">
                                                            <?php foreach ($quotes as $quote): ?>
                                                                <div class="small text-muted mb-2 evidence-quote">
                                                                    “<?= Html::encode($quote) ?>”
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($analysis['inference_rules_applied'])): ?>
                                                        <p class="small text-muted mb-1">
                                                            <strong>Analytical Logic:</strong>
                                                            <?= Html::encode(
                                                                is_array($analysis['inference_rules_applied'])
                                                                    ? implode(', ', $analysis['inference_rules_applied'])
                                                                    : $analysis['inference_rules_applied']
                                                            ) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if (!empty($analysis['uncertainty_factors'])): ?>
                                                        <p class="small text-warning mb-3">
                                                            <strong>Uncertainty Factors:</strong>
                                                            <?= Html::encode(
                                                                is_array($analysis['uncertainty_factors'])
                                                                    ? implode(', ', $analysis['uncertainty_factors'])
                                                                    : $analysis['uncertainty_factors']
                                                            ) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <hr class="my-3">
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-4 d-none"
                                        data-bs-toggle="modal" data-bs-target="#modal-<?= Html::encode($model->request_id) ?>">
                                    View Related Posts <i class="bi bi-arrow-right-short"></i>
                                </button>

                                <a href="<?= Url::to(['view', 'request_id' => $model->request_id]) ?>"
                                   class="btn btn-dark rounded-pill float-end mb-3">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-<?= Html::encode($model->request_id) ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header p-4">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-database-fill me-2"></i> Evidence Log: <?= Html::encode($model->keyword) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body bg-light p-4 p-md-5">
                            <div class="container-fluid">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h3 class="fw-bold">Related Post Records</h3>
                                        <p class="text-muted">
                                            Source data used to generate this intelligence score.
                                        </p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <?php
                                    $foundPosts = false;
                                    if (!empty($relatedPosts)):
                                        foreach ($relatedPosts as $post):
                                            if ($post->request_id === $model->request_id):
                                                $foundPosts = true;
                                                $engagement = Json::decode($post->engagement);
                                                if (!is_array($engagement)) {
                                                    $engagement = [];
                                                }
                                                ?>
                                                <div class="col-md-6 col-xl-4">
                                                    <div class="card h-100 shadow-sm border-0 rounded-3">
                                                        <div class="card-body p-4">
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <span class="badge bg-light text-dark border">
                                                                    <?= Html::encode(strtoupper($post->platform ?? 'N/A')) ?>
                                                                </span>
                                                                <small class="text-muted">
                                                                    <?= !empty($post->created_at) ? date('M d, Y H:i', strtotime($post->created_at)) : 'N/A' ?>
                                                                </small>
                                                            </div>

                                                            <div class="small text-muted mb-2">
                                                                <strong>Author:</strong>
                                                                <span class="masked-value"
                                                                      data-real="<?= Html::encode($post->author ?? 'Unknown') ?>"
                                                                      data-masked="<?= Html::encode(GlobalHelper::PartialMask($post->author ?? 'Unknown')) ?>">
                                                                    <?= Html::encode(GlobalHelper::PartialMask($post->author ?? 'Unknown')) ?>
                                                                </span>
                                                            </div>

                                                            <?php if (!empty($post->location)): ?>
                                                                <div class="small text-muted mb-2">
                                                                    <strong>Location:</strong> <?= Html::encode($post->location) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <p class="small text-dark mb-3">
                                                                <?= Html::encode($post->text ?? '') ?>
                                                            </p>

                                                            <?php if (!empty($engagement)): ?>
                                                                <div class="small text-muted mb-3">
                                                                    <strong>Engagement:</strong>
                                                                    <?= Html::encode(json_encode($engagement)) ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (!empty($post->url)): ?>
                                                                <a href="<?= Html::encode($post->url) ?>" target="_blank"
                                                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Source
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>

                                    <?php if (!$foundPosts): ?>
                                        <div class="col-12">
                                            <div class="alert alert-light border text-center">
                                                No related post records found for this request.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$fetchUrl = Url::to(['fetch']);
$csrfToken = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('osint-search-form');
    const keywordInput = document.getElementById('keyword-input');
    const loadingSpinner = document.getElementById('loading-spinner');
    const resultsContainer = document.getElementById('results-container');
    const tokenRange = document.getElementById('max_tokens');
    const tokenDisplay = document.getElementById('token-display');
    const lengthLabel = document.getElementById('length-label');
    const costDisplay = document.getElementById('cost-display');
    const toggleAllMasksBtn = document.getElementById('toggleAllMasks');

    let showRealValues = false;
    let scanStageInterval = null;

    function updateAllMaskedValues() {
        document.querySelectorAll('.masked-value').forEach(function (element) {
            const realValue = element.dataset.real || '';
            const maskedValue = element.dataset.masked || '';
            element.textContent = showRealValues ? realValue : maskedValue;
        });

        document.querySelectorAll('.toggle-mask i').forEach(function (icon) {
            icon.className = showRealValues ? 'bi bi-eye-slash' : 'bi bi-eye';
        });

        if (toggleAllMasksBtn) {
            toggleAllMasksBtn.innerHTML = showRealValues
                ? '<i class="bi bi-eye-slash"></i> Hide Usernames'
                : '<i class="bi bi-eye"></i> Show Usernames';
        }
    }

    if (toggleAllMasksBtn) {
        toggleAllMasksBtn.addEventListener('click', function () {
            showRealValues = !showRealValues;
            updateAllMaskedValues();
        });
    }

    document.querySelectorAll('.toggle-mask').forEach(function (button) {
        button.addEventListener('click', function () {
            showRealValues = !showRealValues;
            updateAllMaskedValues();
        });
    });

    function resetScanStages() {
        const stages = Array.from(document.querySelectorAll('.scan-stage'));
        stages.forEach((stage, index) => {
            stage.classList.remove('active', 'border-success', 'text-success', 'bg-success-subtle');
            if (index === 0) {
                stage.classList.add('active');
            }
        });
    }

    function runScanStages() {
        const stages = Array.from(document.querySelectorAll('.scan-stage'));
        let activeIndex = 0;

        resetScanStages();

        if (scanStageInterval) {
            clearInterval(scanStageInterval);
        }

        scanStageInterval = setInterval(() => {
            stages.forEach((stage, index) => {
                stage.classList.toggle('active', index === activeIndex);
            });

            if (activeIndex < stages.length - 1) {
                activeIndex++;
            }
        }, 3200);
    }

    function stopScanStages() {
        if (scanStageInterval) {
            clearInterval(scanStageInterval);
            scanStageInterval = null;
        }
    }

    function markScanSuccess() {
        const loadingTitle = document.querySelector('#loading-spinner h5');
        const loadingText = document.querySelector('#loading-spinner p');
        const stages = document.querySelectorAll('.scan-stage');

        stopScanStages();

        stages.forEach(stage => {
            stage.classList.remove('active');
            stage.classList.add('border-success', 'text-success', 'bg-success-subtle');
        });

        if (loadingTitle) {
            loadingTitle.innerText = 'Intelligence Brief Ready';
        }

        if (loadingText) {
            loadingText.innerText = 'Signals collected, correlated, and promoted to the dashboard. Refreshing results...';
        }
    }

    function resetLoadingPanelText() {
        const loadingTitle = document.querySelector('#loading-spinner h5');
        const loadingText = document.querySelector('#loading-spinner p');

        if (loadingTitle) {
            loadingTitle.innerText = 'Generating Intelligence Brief';
        }

        if (loadingText) {
            loadingText.innerText = 'Collecting multi-platform signals, correlating narratives, decoding threat language, and producing an analyst-ready brief.';
        }
    }

    function updateMetrics() {
        const val = parseInt(tokenRange.value, 10) || 2000;
        const model = document.querySelector('input[name="ai_model"]:checked')?.value || 'gpt-4o';

        tokenDisplay.innerText = val.toLocaleString();

        if (val < 1000) {
            lengthLabel.innerText = 'Brief Snippet';
        } else if (val < 4000) {
            lengthLabel.innerText = 'Standard Report';
        } else if (val < 5000) {
            lengthLabel.innerText = 'Detailed Briefing';
        } else {
            lengthLabel.innerText = 'Comprehensive Dossier';
        }

        const pricing = {
            'gpt-4o-mini': 0.0008,
            'gpt-4o': 0.005,
            'gpt-5-thinking': 0.015,
            'gpt-5-nano': 0.0005,
            'gpt-5-mini': 0.003,
            'gpt-5': 0.02
        };

        const rate = pricing[model] || 0.005;
        const estimate = (val / 1000) * rate;

        costDisplay.classList.remove(
            'bg-primary-subtle',
            'text-primary',
            'bg-success-subtle',
            'text-success'
        );

        if (estimate > 0 && estimate < 0.01) {
            costDisplay.innerText = 'Est: < $0.01';
            costDisplay.classList.add('bg-success-subtle', 'text-success');
        } else {
            costDisplay.innerText = `Est: $${estimate.toFixed(2)}`;
            costDisplay.classList.add('bg-primary-subtle', 'text-primary');
        }
    }

    if (tokenRange) {
        tokenRange.addEventListener('input', updateMetrics);
    }

    document.querySelectorAll('input[name="ai_model"]').forEach(function (radio) {
        radio.addEventListener('change', updateMetrics);
    });

    updateMetrics();

    document.querySelectorAll('[data-keyword]').forEach(function (button) {
        button.addEventListener('click', function () {
            keywordInput.value = this.dataset.keyword;
            form.dispatchEvent(new Event('submit', {
                bubbles: true,
                cancelable: true
            }));
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('<?= $csrfParam ?>', '<?= $csrfToken ?>');

            const keyword = (formData.get('keyword') || '').trim();
            if (!keyword) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Keyword',
                        text: 'Please enter a keyword or choose a threat scenario.'
                    });
                } else {
                    alert('Please enter a keyword or choose a threat scenario.');
                }
                return;
            }

            resetLoadingPanelText();
            resetScanStages();

            loadingSpinner.classList.remove('d-none');
            loadingSpinner.scrollIntoView({ behavior: 'smooth', block: 'center' });

            if (resultsContainer) {
                resultsContainer.classList.add('opacity-50');
            }

            runScanStages();

            fetch('<?= $fetchUrl ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    markScanSuccess();

                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    stopScanStages();
                    loadingSpinner.classList.add('d-none');

                    if (resultsContainer) {
                        resultsContainer.classList.remove('opacity-50');
                    }

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Scan Failed',
                            text: data.error || 'Failed to fetch OSINT data.'
                        });
                    } else {
                        alert(data.error || 'Failed to fetch OSINT data.');
                    }
                }
            })
            .catch(error => {
                stopScanStages();
                loadingSpinner.classList.add('d-none');

                if (resultsContainer) {
                    resultsContainer.classList.remove('opacity-50');
                }

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Error',
                        text: error.message || 'An unexpected error occurred.'
                    });
                } else {
                    alert(error.message || 'An unexpected error occurred.');
                }
            });
        });
    }

    const chartCanvas = document.getElementById('platformChart');
    if (chartCanvas && window.Chart) {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($metrics['platformLabels']) ?>,
                datasets: [{
                    data: <?= json_encode($metrics['platformData']) ?>,
                    backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14'],
                    hoverOffset: 10,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                cutout: '70%'
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