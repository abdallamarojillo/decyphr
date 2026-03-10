<?php

use app\helpers\GlobalHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'OSINT Intelligence Feed';
$relatedCount = 0;
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h2 class="fw-bold tracking-tight mb-1 text-dark">
                Social Media Threat Intelligence <span class="text-primary">Dashboard</span>
            </h2>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted fw-medium">
                    <i class="bi bi-cpu-fill me-1"></i> Multi-platform Analysis:
                </small>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">X</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">Facebook</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">TikTok</span>
                <span class="badge bg-light text-dark border-0 shadow-none px-2 py-1">Reddit</span>
            </div>
        </div>

        <div class="report-counter p-3 shadow-sm border text-center">
            <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1" style="font-size: 0.65rem;">Analyzed
                Reports</div>
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
            <div class="card border-0 shadow-sm rounded-4 h-100">
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
            <div class="card border-0 shadow-sm rounded-4 h-100">
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
        <div class="col-md-12 float-end">
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
            <!-- Entity Mapping Table -->
            <div class="card border-0 shadow-sm rounded-4 h-100">
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
                                <tr>
                                    <td class="ps-4 fw-bold">

                                        <span class="masked-value" data-real="<?= Html::encode($user) ?>"
                                            data-masked="<?= GlobalHelper::PartialMask($user) ?>">

                                            <?= GlobalHelper::PartialMask($user) ?>
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
                                            <?php
                                        // calculate relative width for progress (max = highest count)
                                        $maxCount = max(array_column($userMap, 'count'));
                                        $width = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0;
                                        ?>
                                            <div class="progress-bar bg-danger" style="width: <?= $width ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= implode(', ', array_unique($data['platforms'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No entity mapping available.
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

    <div class="mb-4">
        <label class="small fw-bold text-uppercase text-muted mb-2 d-block"
            style="font-size: 0.7rem; letter-spacing: 1px;">Quick filters</label>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-danger btn-sm tactical-btn modern-pill"
                data-keyword="al shabaab terrorism attack Kenya">
                <i class="bi bi-shield-lock-fill me-1"></i> Terrorism
            </button>
            <button class="btn btn-outline-warning btn-sm tactical-btn modern-pill"
                data-keyword="kidnapped abducted ransom Kenya">
                <i class="bi bi-person-exclamation me-1"></i> Kidnapping
            </button>
            <button class="btn btn-outline-dark btn-sm tactical-btn modern-pill"
                data-keyword="gang violence shooting Kenya">
                <i class="bi bi-geo-alt-fill me-1"></i> Gangs
            </button>
        </div>
    </div>

    <form id="osint-search-form" class="mb-5">
        <div class="search-container shadow-sm">
            <div class="input-group">
                <span class="input-group-text border-0 bg-transparent ps-4">
                    <i class="bi bi-terminal text-primary"></i>
                </span>
                <input id="keyword-input" type="text" class="form-control border-0 bg-transparent py-3 shadow-none"
                    placeholder="Search keyword (e.g. gang violence, protests...)">
                <button class="btn btn-primary px-5 fw-bold scan-button" type="submit">SCAN</button>
            </div>
        </div>
    </form>

    <div id="loading-spinner" class="text-center py-5 d-none">
        <div class="spinner-border text-primary"></div>
        <div class="mt-2 text-muted">Analyzing data sources…</div>
    </div>

    <div id="results-container" class="row g-4">
        <?php foreach ($osintaidata as $model): 
            $report = Json::decode($model->report);
            $score = (int)$model->numerical_score;
            $statusColor = ($score >= 70) ? 'danger' : (($score >= 40) ? 'warning' : 'success');
            $statusLabel = ($score >= 70) ? 'CRITICAL' : (($score >= 40) ? 'ELEVATED' : 'STABLE');
            
            // Logic for the Map
           $primaryLoc = !empty($report['localized_risks']) 
            ? $report['localized_risks'][0]['location'] 
            : 'Kenya';

            $mapEmbedUrl = "https://www.google.com/maps?q=" . urlencode($primaryLoc . ", Kenya") . "&output=embed";
            $mapRedirectUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($primaryLoc . ", Kenya");
        ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden rounded-4 mb-4">
                <div class="row g-0">
                    <div
                        class="col-md-1 bg-<?= $statusColor ?> d-flex flex-column justify-content-center align-items-center text-white py-4">
                        <small class="fw-bold opacity-75 rotate-text mb-2">SCORE</small>
                        <h2 class="fw-black mb-0"><?= $score ?></h2>
                    </div>

                    <div class="col-md-11">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <span class="badge rounded-pill bg-light text-dark border mb-2">ID:
                                        <?= $model->request_id ?></span>
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

                                <!-- Executive Summary -->
                                <div class="col-md-12">
                                    <div class="p-4 rounded-4 border bg-white shadow-lg">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                                                    <i class="fa fa-file fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h6 class="text-uppercase text-muted fw-bold small mb-1">
                                                    Executive Summary
                                                </h6>
                                                <div class="lead fs-6 text-dark">
                                                    <?= nl2br(Html::encode($report['threat_summary'] ?? 'Clean - No threats detected.')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Geographic Threat Vectors -->
                                <div class="col-md-6 border-end">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-0">
                                            Geographic Threat Vectors
                                        </h6>

                                        <button class="btn btn-sm btn-outline-primary border-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#map-<?= $model->id ?>">
                                            <i class="bi bi-geo-alt"></i> Toggle Map
                                        </button>
                                    </div>

                                    <div class="collapse mb-3" id="map-<?= $model->id ?>">
                                        <div class="rounded-3 overflow-hidden border shadow-sm position-relative"
                                            style="height:220px;">

                                            <iframe width="100%" height="100%" frameborder="0" loading="lazy"
                                                src="<?= $mapEmbedUrl ?>" allowfullscreen></iframe>

                                            <a href="<?= $mapRedirectUrl ?>" target="_blank"
                                                class="btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2 shadow-sm fw-bold">
                                                <i class="bi bi-cursor-fill me-1"></i> Open in Maps
                                            </a>
                                        </div>
                                    </div>


                                    <?php
        $risks = $report['localized_risks'] ?? [];
        if (!is_array($risks)) $risks = [$risks];
        ?>

                                    <?php if (!empty($risks)): ?>

                                    <div class="list-group list-group-flush">

                                        <?php foreach (array_slice($risks,0,3) as $risk): ?>

                                        <div class="list-group-item px-0 border-0 bg-transparent mb-2">

                                            <div class="d-flex justify-content-between">

                                                <strong class="text-dark small">
                                                    <?= Html::encode($risk['location'] ?? '') ?>
                                                </strong>

                                                <?php if (!empty($risk['severity'])): ?>

                                                <span
                                                    class="badge rounded-pill bg-light text-<?= ($risk['severity']=='High')?'danger':'warning' ?> border small">
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

                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($risk['location'].', Kenya') ?>"
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



                                <!-- Signals & Intelligence -->
                                <div class="col-md-6">

                                    <h6 class="text-uppercase fw-bold text-muted small mb-3">
                                        Signals & Intelligence
                                    </h6>


                                    <!-- Decoded Language -->
                                    <?php
        $decoded = $report['decoded_language'] ?? [];
        if (!is_array($decoded)) $decoded = [$decoded];
        ?>

                                    <?php if (!empty($decoded)): ?>

                                    <div class="d-flex flex-wrap gap-2 mb-3">

                                        <?php foreach ($decoded as $lang): ?>

                                        <span class="badge bg-white text-dark border p-2 fw-normal"
                                            title="<?= Html::encode($lang['contextual_explanation'] ?? '') ?>">

                                            <span class="text-primary fw-bold">
                                                <?= Html::encode($lang['original_term'] ?? '') ?>:
                                            </span>

                                            <?= Html::encode($lang['decoded_meaning'] ?? '') ?>

                                        </span>

                                        <?php endforeach; ?>

                                    </div>

                                    <?php endif; ?>


                                    <!-- Dog Whistles -->
                                    <?php
$dogWhistles = $report['dog_whistles'] ?? [];

if (!is_array($dogWhistles)) {
    $dogWhistles = [$dogWhistles];
}
?>

                                    <?php if (!empty($dogWhistles)): ?>

                                    <ul class="small text-muted mb-0">

                                        <?php foreach ($dogWhistles as $dw): ?>

                                        <li>

                                            <?php
if (is_array($dw)) {
    echo Html::encode(implode(', ', array_map('strval', $dw)));
} else {
    echo Html::encode($dw);
}
?>

                                        </li>

                                        <?php endforeach; ?>

                                    </ul>

                                    <?php endif; ?>


                                    <!-- Surveillance Suggestions -->
                                    <?php
        $locations = $report['location_suggestions'] ?? [];
        if (!is_array($locations)) $locations = [$locations];
        ?>

                                    <?php if (!empty($locations)): ?>

                                    <div class="bg-light rounded p-3 mb-3">

                                        <p class="fw-bold small mb-1">
                                            Surveillance Recommendations:
                                        </p>

                                        <ul class="list-unstyled mb-0">

                                            <?php foreach ($locations as $loc): ?>

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


                                    <!-- Risk Metrics -->
                                    <?php if (!empty($report['risk_trajectory']) || !empty($report['numerical_score'])): ?>

                                    <div class="bg-light rounded p-3">

                                        <?php if (!empty($report['risk_trajectory'])): ?>

                                        <p class="small mb-1">
                                            <strong>Risk Trajectory:</strong>
                                            <?= Html::encode($report['risk_trajectory']) ?>
                                        </p>

                                        <?php endif; ?>

                                        <?php if (!empty($report['numerical_score'])): ?>

                                        <p class="small mb-0">
                                            <strong>Threat Score:</strong>
                                            <?= Html::encode($report['numerical_score']) ?>/10
                                        </p>

                                        <?php endif; ?>

                                    </div>

                                    <?php endif; ?>

                                </div>

                            </div>



                            <!-- Analyst Assessment -->
                            <?php
$analysisList = $report['analysis_basis'] ?? [];
if (!is_array($analysisList)) $analysisList = [$analysisList];
?>

                            <?php if (!empty($analysisList)): ?>

                            <div class="row g-4 mb-4">

                                <div class="col-md-12">

                                    <div class="p-4 rounded-4 border bg-white shadow-sm">

                                        <h6 class="text-uppercase fw-bold text-muted small mb-3">
                                            Analyst Assessment
                                        </h6>

                                        <?php foreach ($analysisList as $analysis): ?>

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
                if (!is_array($quotes)) $quotes = [$quotes];
                ?>

                                        <?php if (!empty($quotes)): ?>

                                        <ul class="small text-muted mb-2">

                                            <?php foreach ($quotes as $quote): ?>

                                            <li>"<?= Html::encode($quote) ?>"</li>

                                            <?php endforeach; ?>

                                        </ul>

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

                                        <p class="small text-warning mb-0">

                                            <strong>Uncertainty Factors:</strong>

                                            <?= Html::encode(
                            is_array($analysis['uncertainty_factors'])
                                ? implode(', ', $analysis['uncertainty_factors'])
                                : $analysis['uncertainty_factors']
                        ) ?>

                                        </p>

                                        <?php endif; ?>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            </div>

                            <?php endif; ?>

                            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-4"
                                data-bs-toggle="modal" data-bs-target="#modal-<?= $model->request_id ?>">
                                View Related Posts <i class="bi bi-arrow-right-short"></i>
                            </button>

                            <a href="<?= Url::to(['view', 'request_id' => $model->request_id]) ?>"
                                class="btn btn-dark rounded-pill float-end">View Details</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-<?= $model->request_id ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header p-4">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-database-fill me-2"></i> Evidence Log: <?= Html::encode($model->keyword) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-light p-5">
                        <div class="container">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h3 class="fw-bold">Related Post Records</h3>
                                    <p class="text-muted">Source data used to generate Intelligence Score:
                                        <strong></strong>
                                    </p>
                                </div>
                            </div>
                            <div class="row g-3">
                                <?php 
                                    $foundPosts = false;
                                    foreach ($relatedPosts as $post): 
                                        if ($post->request_id === $model->request_id): 
                                            $foundPosts = true;
                                            $engagement = Json::decode($post->engagement);
                                    ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 shadow-sm border-0 rounded-3">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between mb-3">
                                                <span
                                                    class="badge bg-primary-subtle text-primary text-uppercase"><?= $post->platform ?></span>
                                                <small
                                                    class="text-muted"><?= date('M d, Y', strtotime($post->created_at)) ?></small>
                                            </div>
                                            <h6 class="fw-bold mb-4">
                                                <span class="masked-value mb-3"
                                                    data-real="<?= Html::encode($post->author) ?>"
                                                    data-masked="<?= GlobalHelper::PartialMask($post->author) ?>">

                                                    <?= GlobalHelper::PartialMask($post->author) ?>
                                                </span>

                                                <button class="btn btn-sm toggle-mask ms-2 float-end">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </h6>
                                            <p class="card-text text-secondary small mb-3 italic">
                                                "<?= Html::encode($post->text) ?>"</p>
                                            <div class="border-top pt-3 d-flex gap-3">
                                                <span class="small"><i class="bi bi-hand-thumbs-up me-1"></i>
                                                    <?= $engagement['likes'] ?? 0 ?></span>
                                                <span class="small"><i class="bi bi-share me-1"></i>
                                                    <?= $engagement['shares'] ?? 0 ?></span>
                                                <span class="small"><i class="bi bi-chat me-1"></i>
                                                    <?= $engagement['comments'] ?? 0 ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; endforeach; ?>

                                <?php if (!$foundPosts): ?>
                                <div class="col-12 text-center py-5">
                                    <i class="bi bi-folder-x display-1 text-muted"></i>
                                    <p class="mt-3">No specific social posts linked to this Analysis ID.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
/* CORE AESTHETICS */
:root {
    --osint-blue: #0d6efd;
    --osint-bg: #f8f9fa;
}

.tracking-tight {
    letter-spacing: -0.02em;
}

.fw-black {
    font-weight: 900;
}

.rotate-text {
    writing-mode: vertical-lr;
    transform: rotate(180deg);
    font-size: 0.65rem;
    letter-spacing: 1px;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, .1) !important;
}

/* MAP & NAV ELEMENTS */
.x-small {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.list-group-item:hover {
    background-color: rgba(0, 0, 0, 0.02) !important;
}

/* COMPACT COMPONENTS */
.report-counter {
    background: #fff;
    border-radius: 12px;
    min-width: 140px;
    border: 1px solid #e9ecef !important;
}

.modern-pill {
    border-radius: 50px !important;
    padding: 6px 16px !important;
    font-weight: 600 !important;
    font-size: 0.7rem !important;
    text-transform: uppercase;
}

.search-container {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 16px;
    overflow: hidden;
}

.search-container:focus-within {
    border-color: var(--osint-blue);
    box-shadow: 0 8px 30px rgba(13, 110, 253, 0.12) !important;
}

.scan-button {
    border-radius: 10px !important;
    margin: 4px;
}

.bg-danger-subtle {
    background-color: #f8d7da;
}

.bg-warning-subtle {
    background-color: #fff3cd;
}

.bg-success-subtle {
    background-color: #d1e7dd;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const spinner = document.getElementById('loading-spinner');
const results = document.getElementById('results-container');

// Search Logic
document.getElementById('osint-search-form').onsubmit = e => {
    e.preventDefault();
    spinner.classList.remove('d-none');
    results.classList.add('opacity-50');

    fetch('<?= Url::to(['osint/fetch']) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'
            },
            body: 'keyword=' + encodeURIComponent(document.getElementById('keyword-input').value)
        })
        .then(r => r.json())
        .then(() => {
            location.reload();
        });
};

// Quick Filters
document.querySelectorAll('.tactical-btn').forEach(b => {
    b.onclick = () => {
        document.getElementById('keyword-input').value = b.dataset.keyword;
        document.getElementById('osint-search-form').dispatchEvent(new Event('submit'));
    };
});

document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('platformChart').getContext('2d');
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

    $.fn.dataTable.ext.errMode = 'none';

    $('.user-mapping-table').DataTable({
        pageLength: 5,
        lengthMenu: [
            [5, 10, 25, 50, -1],
            [5, 10, 25, 50, "All"]
        ],
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf'],
        columnDefs: [{
            targets: '_all',
            defaultContent: ''
        }]
    });
});
</script>