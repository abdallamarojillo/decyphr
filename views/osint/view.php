<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap5\ActiveForm;


$this->title = 'OSINT Intelligence Feed Request ID '.$osintaidata[0]['request_id'];
$relatedCount = 0;
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h2 class="fw-bold tracking-tight mb-1 text-dark">
                View Post <span class="text-primary">Request ID: <?= $osintaidata[0]['request_id'] ?></span>
            </h2>
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
                                    <td class="ps-4 fw-bold"><?= Html::encode($user) ?></td>
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

    <?php  
    $form = ActiveForm::begin([
        'id' => 'risk-score-form',
        'options' => ['class' => 'p-3'],
        'action' => 'manually-update-threat-score'
    ]); ?>

    <input type="hidden" name="request_id" value="<?= $osintaidata[0]['request_id'] ?>">

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div id="risk-top-border" class="py-1"></div>

        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="fw-bold text-uppercase text-secondary mb-1" style="letter-spacing: 1px;">
                        Risk Assessment
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
                            <?= $osintaidata[0]['numerical_score'] ?> %</span>
                    </div>
                    <span class="badge bg-white text-dark border shadow-sm px-2">High</span>
                </div>

                <input type="range" name="threat_score" class="form-range" min="0" max="100"
                    value="<?= $osintaidata[0]['numerical_score'] ?>" id="rangescore"
                    oninput="updateRiskUI(this.value)">

                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted fw-light">Minimal Threat</small>
                    <small class="text-muted fw-light">Critical Threat</small>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-dark rounded-pill px-5 py-2 fw-bold shadow-sm border-0">
                    UPDATE RATING
                </button>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

<div class="row g-0">
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

                    <?php  
                    $form = ActiveForm::begin([
                        'id' => 'resubmit-analysis',
                        'options' => ['class' => 'p-3'],
                        'action' => ['reanalyze', 'request_id' => $osintaidata[0]['request_id']]
                    ]); ?>
                    <input type="hidden" name="request_id" value="<?= $osintaidata[0]['request_id'] ?>">

                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="fal fa-history"></i> RESUBMIT FOR DEEP ANALYSIS
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
                                <div class="col-md-12">
                                    <div class="p-4 rounded-4 border bg-white shadow-lg">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                                                    <i class="fa fa-file fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h6 class="text-uppercase text-muted fw-bold small mb-1">Executive
                                                    Summary</h6>
                                                <div class="lead fs-6 text-dark">
                                                    <?= nl2br(htmlspecialchars($report['threat_summary'] ?? 'Clean - No threats detected.')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 border-end">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-0">Geographic Threat
                                            Vectors</h6>
                                        <button class="btn btn-sm btn-outline-primary border-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#map-<?= $model->id ?>">
                                            <i class="bi bi-geo-alt"></i> Toggle Map
                                        </button>
                                    </div>

                                    <div class="collapse mb-3" id="map-<?= $model->id ?>">
                                        <div class="rounded-3 overflow-hidden border shadow-sm position-relative"
                                            style="height: 220px;">
                                            <iframe width="100%" loading="lazy" height="100%" frameborder="0"
                                                src="<?= $mapEmbedUrl ?>" allowfullscreen></iframe>
                                            <a href="<?= $mapRedirectUrl ?>" target="_blank"
                                                class="btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2 shadow-sm fw-bold">
                                                <i class="bi bi-cursor-fill me-1"></i> Open in Maps
                                            </a>
                                        </div>
                                    </div>

                                    <?php if (!empty($report['localized_risks'])): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($report['localized_risks'], 0, 3) as $risk): ?>
                                        <div class="list-group-item px-0 border-0 bg-transparent mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong
                                                    class="text-dark small"><?= Html::encode($risk['location']) ?></strong>
                                                <span
                                                    class="badge rounded-pill bg-light text-<?= $risk['severity'] == 'High' ? 'danger' : 'warning' ?> border small">
                                                    <?= $risk['severity'] ?>
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-1">
                                                <?= Html::encode($risk['risk_description']) ?></p>
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($risk['location'] . ", Kenya") ?>"
                                                target="_blank" class="text-primary x-small text-decoration-none">
                                                <i class="bi bi-arrow-up-right-circle me-1"></i> Open to Map
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-light py-2 small">No specific geographic risks identified.
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-uppercase fw-bold text-muted small mb-3">Signals & Intelligence</h6>

                                    <?php if (!empty($report['decoded_language'])): ?>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach ($report['decoded_language'] as $lang): ?>
                                        <span class="badge bg-white text-dark border p-2 fw-normal"
                                            title="<?= Html::encode($lang['contextual_explanation']) ?>">
                                            <span
                                                class="text-primary fw-bold"><?= Html::encode($lang['original_term']) ?>:</span>
                                            <?= Html::encode($lang['decoded_meaning']) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($report['location_suggestions'])): ?>
                                    <div class="bg-light rounded p-3">
                                        <p class="fw-bold small mb-1">Surveillance Recommendations:</p>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($report['location_suggestions'] as $loc): ?>
                                            <li class="small text-muted mb-1">• <span
                                                    class="text-dark fw-medium"><?= Html::encode($loc['location_name']) ?></span>:
                                                <?= Html::encode($loc['reason']) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-4"
                                        data-bs-toggle="modal" data-bs-target="#modal-<?= $model->request_id ?>">
                                        View Related Posts <i class="bi bi-arrow-right-short"></i>
                                    </button>
                                </div>
                            </div>


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
                                            <h6 class="fw-bold mb-2"><?= Html::encode($post->author) ?></h6>
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
                                        <div class="card-footer">
                                            <?php 
                                            $form = ActiveForm::begin([
                                                'id' => 'delete-osint-post-form-'.$post->id,
                                                'layout' => 'default',
                                                'action' => 'delete-osint-post'
                                            ]);
                                            ?>
                                            <input type="hidden" name="id" value="<?= $post->id ?>">
                                            <input type="hidden" name="request_id" value="<?= $post->request_id ?>">
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-danger rounded-pill"> Exclude</button>
                                                <small class="text-muted ms-1">
                                                    This will exclude this post from analysis results.
                                                </small>
                                            </div>
                                            <?php ActiveForm::end(); ?>
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

<?php
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

    let form  = $(this);

    Swal.fire({
        title: 'Confirm Resubmission',
        text: 'Resubmit this data for AI Analysis?  ',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'YES, RESUBMIT',
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

JS;

$this->registerJs($js);
?>




<script>
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

function updateRiskUI(val) {
    const output = document.getElementById('rangescorevalue');
    const badge = document.getElementById('risk-badge');
    output.innerHTML = val + '%';

    if (val < 30) {
        badge.innerHTML = "Low Risk";
        badge.className = "badge rounded-pill bg-success px-3 py-2";
    } else if (val < 70) {
        badge.innerHTML = "Moderate Risk";
        badge.className = "badge rounded-pill bg-warning text-dark px-3 py-2";
    } else {
        badge.innerHTML = "High Risk";
        badge.className = "badge rounded-pill bg-danger px-3 py-2";
    }
}
</script>