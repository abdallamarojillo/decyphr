<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'OSINT Intelligence Feed';
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
            </div>
        </div>
        
        <div class="report-counter p-3 shadow-sm border text-center">
            <div class="small fw-bold text-uppercase text-muted opacity-75 mb-1" style="font-size: 0.65rem;">Analyzed Reports</div>
            <div class="h4 m-0 fw-black text-primary"><?= count($osintaidata) ?></div>
        </div>
    </div>

    <hr class="opacity-10 mb-4">

    <div class="mb-4">
        <label class="small fw-bold text-uppercase text-muted mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Quick filters</label>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-danger btn-sm tactical-btn modern-pill" data-keyword="al shabaab terrorism attack Kenya">
                <i class="bi bi-shield-lock-fill me-1"></i> Terrorism
            </button>
            <button class="btn btn-outline-warning btn-sm tactical-btn modern-pill" data-keyword="kidnapped abducted ransom Kenya">
                <i class="bi bi-person-exclamation me-1"></i> Kidnapping
            </button>
            <button class="btn btn-outline-dark btn-sm tactical-btn modern-pill" data-keyword="gang violence shooting Kenya">
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
                <input id="keyword-input" type="text" class="form-control border-0 bg-transparent py-3 shadow-none" placeholder="Search keyword (e.g. gang violence, protests...)">
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
            $primaryLoc = !empty($report['localized_risks']) ? $report['localized_risks'][0]['location'] : 'Kenya';
            $mapEmbedUrl = "https://maps.google.com/maps?q=" . urlencode($primaryLoc . ", Kenya") . "&t=&z=13&ie=UTF8&iwloc=&output=embed";
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
                                        <span class="badge rounded-pill bg-light text-dark border mb-2">ID: <?= $model->request_id ?></span>
                                        <h4 class="card-title fw-bold mb-1 text-uppercase">
                                            <i class="bi bi-shield-exclamation me-2"></i><?= Html::encode($model->keyword) ?>
                                        </h4>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock me-1"></i> Analysis Date: <?= date('M d, Y - H:i', strtotime($model->analyzed_at)) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?> px-3 py-2">
                                            STATUS: <?= $statusLabel ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="text-uppercase fw-bold text-muted small mb-0">Geographic Threat Vectors</h6>
                                            <button class="btn btn-sm btn-outline-primary border-0" type="button" data-bs-toggle="collapse" data-bs-target="#map-<?= $model->id ?>">
                                                <i class="bi bi-geo-alt"></i> Toggle Map
                                            </button>
                                        </div>

                                        <div class="collapse mb-3" id="map-<?= $model->id ?>">
                                            <div class="rounded-3 overflow-hidden border shadow-sm position-relative" style="height: 220px;">
                                                <iframe width="100%" height="100%" frameborder="0" src="<?= $mapEmbedUrl ?>" allowfullscreen></iframe>
                                                <a href="<?= $mapRedirectUrl ?>" target="_blank" class="btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2 shadow-sm fw-bold">
                                                    <i class="bi bi-cursor-fill me-1"></i> Open in Maps
                                                </a>
                                            </div>
                                        </div>

                                        <?php if (!empty($report['localized_risks'])): ?>
                                            <div class="list-group list-group-flush">
                                                <?php foreach (array_slice($report['localized_risks'], 0, 3) as $risk): ?>
                                                    <div class="list-group-item px-0 border-0 bg-transparent mb-2">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="text-dark small"><?= Html::encode($risk['location']) ?></strong>
                                                            <span class="badge rounded-pill bg-light text-<?= $risk['severity'] == 'High' ? 'danger' : 'warning' ?> border small">
                                                                <?= $risk['severity'] ?>
                                                            </span>
                                                        </div>
                                                        <p class="text-muted small mb-1"><?= Html::encode($risk['risk_description']) ?></p>
                                                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($risk['location'] . ", Kenya") ?>" target="_blank" class="text-primary x-small text-decoration-none">
                                                            <i class="bi bi-arrow-up-right-circle me-1"></i> Open to Map
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-light py-2 small">No specific geographic risks identified.</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-3">Signals & Intelligence</h6>
                                        
                                        <?php if (!empty($report['decoded_language'])): ?>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <?php foreach ($report['decoded_language'] as $lang): ?>
                                                    <span class="badge bg-white text-dark border p-2 fw-normal" title="<?= Html::encode($lang['contextual_explanation']) ?>">
                                                        <span class="text-primary fw-bold"><?= Html::encode($lang['original_term']) ?>:</span> <?= Html::encode($lang['decoded_meaning']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($report['location_suggestions'])): ?>
                                            <div class="bg-light rounded p-3">
                                                <p class="fw-bold small mb-1">Surveillance Recommendations:</p>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($report['location_suggestions'] as $loc): ?>
                                                        <li class="small text-muted mb-1">• <span class="text-dark fw-medium"><?= Html::encode($loc['location_name']) ?></span>: <?= Html::encode($loc['reason']) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modal-<?= $model->request_id ?>">
                                    View Related Posts <i class="bi bi-arrow-right-short"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-<?= $model->request_id ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white p-4">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-database-fill me-2"></i> Evidence Log: <?= Html::encode($model->keyword) ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light p-5">
                            <div class="container">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h3 class="fw-bold">Related Post Records</h3>
                                        <p class="text-muted">Source data used to generate Intelligence Score: <strong><?= $score ?></strong></p>
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
                                                        <span class="badge bg-primary-subtle text-primary text-uppercase"><?= $post->platform ?></span>
                                                        <small class="text-muted"><?= date('M d, Y', strtotime($post->created_at)) ?></small>
                                                    </div>
                                                    <h6 class="fw-bold mb-2"><?= Html::encode($post->author) ?></h6>
                                                    <p class="card-text text-secondary small mb-3 italic">"<?= Html::encode($post->text) ?>"</p>
                                                    <div class="border-top pt-3 d-flex gap-3">
                                                        <span class="small"><i class="bi bi-hand-thumbs-up me-1"></i> <?= $engagement['likes'] ?? 0 ?></span>
                                                        <span class="small"><i class="bi bi-share me-1"></i> <?= $engagement['shares'] ?? 0 ?></span>
                                                        <span class="small"><i class="bi bi-chat me-1"></i> <?= $engagement['comments'] ?? 0 ?></span>
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
:root { --osint-blue: #0d6efd; --osint-bg: #f8f9fa; }
.tracking-tight { letter-spacing: -0.02em; }
.fw-black { font-weight: 900; }
.rotate-text { writing-mode: vertical-lr; transform: rotate(180deg); font-size: 0.65rem; letter-spacing: 1px; }
.card { transition: all 0.3s ease; }
.card:hover { box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }

/* MAP & NAV ELEMENTS */
.x-small { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.list-group-item:hover { background-color: rgba(0,0,0,0.02) !important; }

/* COMPACT COMPONENTS */
.report-counter { background: #fff; border-radius: 12px; min-width: 140px; border: 1px solid #e9ecef !important; }
.modern-pill { border-radius: 50px !important; padding: 6px 16px !important; font-weight: 600 !important; font-size: 0.7rem !important; text-transform: uppercase; }
.search-container { background: #fff; border: 1px solid #dee2e6; border-radius: 16px; overflow: hidden; }
.search-container:focus-within { border-color: var(--osint-blue); box-shadow: 0 8px 30px rgba(13, 110, 253, 0.12) !important; }
.scan-button { border-radius: 10px !important; margin: 4px; }
.bg-danger-subtle { background-color: #f8d7da; }
.bg-warning-subtle { background-color: #fff3cd; }
.bg-success-subtle { background-color: #d1e7dd; }
</style>

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
    .then(() => { location.reload(); });
};

// Quick Filters
document.querySelectorAll('.tactical-btn').forEach(b => {
    b.onclick = () => {
        document.getElementById('keyword-input').value = b.dataset.keyword;
        document.getElementById('osint-search-form').dispatchEvent(new Event('submit'));
    };
});
</script>