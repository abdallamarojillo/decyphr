<?php
use yii\helpers\Url;
$this->title = 'OSINT Dashboard';
?>

<div class="container py-4">

    <!-- HEADER -->
    <div class="mb-4">
        <h2 class="mb-1">
            <i class="fas fa-shield-alt text-primary"></i> OSINT Intelligence Dashboard
        </h2>
        <small class="text-muted">
            Multi-platform threat intelligence (X • Facebook • TikTok)
        </small>
    </div>

    <!-- QUICK KEYWORDS -->
    <div class="mb-3">
        <button class="btn btn-outline-danger btn-sm tactical-btn"
            data-keyword="al shabaab terrorism attack Kenya">
            Terrorism
        </button>
        <button class="btn btn-outline-warning btn-sm tactical-btn"
            data-keyword="kidnapped abducted ransom Kenya">
            Kidnapping
        </button>
        <button class="btn btn-outline-dark btn-sm tactical-btn"
            data-keyword="gang violence shooting Kenya">
            Gangs
        </button>
    </div>

    <!-- SEARCH -->
    <form id="osint-search-form" class="mb-4">
        <div class="input-group shadow-sm">
            <input id="keyword-input" class="form-control" placeholder="Run manual OSINT scan…">
            <button class="btn btn-primary">Scan</button>
        </div>
    </form>

    <!-- LOADER -->
    <div id="loading-spinner" class="text-center py-5 d-none">
        <div class="spinner-border text-primary"></div>
        <div class="mt-2 text-muted">Analyzing data sources…</div>
    </div>

    <!-- RESULTS -->
    <div id="results-container" class="d-none">

        <!-- THREAT OVERVIEW -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body row align-items-center">
                <div class="col-md-9">
                    <small class="text-uppercase text-muted">Threat Summary</small>
                    <p id="ai-summary-text" class="mb-0 intel-summary"></p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="threat-score-circle">
                        <span id="threat-score">0</span>
                    </div>
                    <span id="threat-level-badge"
                        class="badge bg-success mt-2 px-3 py-2">LOW</span>
                </div>
            </div>
        </div>

        <!-- AI INTELLIGENCE -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="intel-panel">
                    <h6> Decoded Language</h6>
                    <ul id="decoded-language"></ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="intel-panel">
                    <h6>Dog Whistles</h6>
                    <ul id="dog-whistles"></ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="intel-panel">
                    <h6> Localized Risks</h6>
                    <ul id="localized-risks"></ul>
                </div>
            </div>
        </div>

        <!-- MAP -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Geographic Focus</div>
            <iframe id="intel-map"
                src="https://www.openstreetmap.org/export/embed.html?bbox=33.5,-4.9,41.9,5.3&layer=mapnik&zoom=6">
            </iframe>
        </div>

        <!-- PLATFORM TABS -->
        <ul class="nav nav-tabs mb-3" id="platformTabs">
            <li class="nav-item"><a class="nav-link active" data-target="x-feed">X (Twitter)</a></li>
            <li class="nav-item"><a class="nav-link" data-target="facebook-feed">Facebook (Context)</a></li>
            <li class="nav-item"><a class="nav-link" data-target="tiktok-feed">TikTok (Narratives)</a></li>
        </ul>

        <div class="row" id="x-feed"></div>
        <div class="row d-none" id="facebook-feed"></div>
        <div class="row d-none" id="tiktok-feed"></div>

    </div>
</div>

<!-- JS -->
<script>
const spinner = document.getElementById('loading-spinner');
const results = document.getElementById('results-container');

document.querySelectorAll('.tactical-btn').forEach(b => {
    b.onclick = () => {
        document.getElementById('keyword-input').value = b.dataset.keyword;
        document.getElementById('osint-search-form').dispatchEvent(new Event('submit'));
    };
});

document.getElementById('osint-search-form').onsubmit = e => {
    e.preventDefault();
    spinner.classList.remove('d-none');
    results.classList.add('d-none');

    fetch('<?= Url::to(['osint/fetch']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'
        },
        body: 'keyword=' + encodeURIComponent(document.getElementById('keyword-input').value)
    })
    .then(r => r.json())
    .then(({data}) => {
        spinner.classList.add('d-none');
        results.classList.remove('d-none');

        // SUMMARY
        document.getElementById('ai-summary-text').textContent =
            data.ai_report?.threat_summary || 'No summary available';

        // SCORE
        const score = Math.round(data.threat_score || 0);
        document.getElementById('threat-score').textContent = score;
        const badge = document.getElementById('threat-level-badge');
        badge.className = 'badge px-3 py-2 mt-2 ' +
            (score > 70 ? 'bg-danger' : score > 40 ? 'bg-warning' : 'bg-success');
        badge.textContent = score > 70 ? 'CRITICAL' : score > 40 ? 'ELEVATED' : 'LOW';

        // AI PANELS
        fillList('decoded-language', data.ai_report?.decoded_language?.translations);
        fillList('dog-whistles', data.ai_report?.dog_whistles);
        fillList('localized-risks', data.ai_report?.localized_risks);

        // FEEDS
        renderFeed('x-feed', data.platforms?.x?.data);
        renderFeed('facebook-feed', data.platforms?.facebook?.data);
        renderFeed('tiktok-feed', data.platforms?.tiktok?.data);
    });
};

function fillList(id, obj) {
    const el = document.getElementById(id);
    el.innerHTML = '';
    if (!obj) return;
    Object.entries(obj).forEach(([k,v]) => {
        el.innerHTML += `<li><strong>${k}</strong>: ${v}</li>`;
    });
}

function renderFeed(id, posts = []) {
    const el = document.getElementById(id);
    el.innerHTML = '';
    posts.slice(0,6).forEach(p => {
        el.innerHTML += `
        <div class="col-md-4 mb-3">
            <div class="intel-card">
                <strong>@${p.author}</strong>
                <div class="text-muted small">${p.created_at}</div>
                <div class="small my-2">${p.text}</div>
            </div>
        </div>`;
    });
}

// TABS
document.querySelectorAll('#platformTabs .nav-link').forEach(tab => {
    tab.onclick = () => {
        document.querySelectorAll('#platformTabs .nav-link')
            .forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        ['x-feed','facebook-feed','tiktok-feed']
            .forEach(id => document.getElementById(id).classList.add('d-none'));
        document.getElementById(tab.dataset.target).classList.remove('d-none');
    };
});
</script>

<!-- CSS -->
<style>
.intel-summary{font-size:.95rem;line-height:1.6}
.threat-score-circle{
    width:90px;height:90px;border-radius:50%;
    background:#0d6efd;color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;font-weight:700;margin:auto
}
.intel-panel{
    background:#fff;border-radius:.5rem;padding:15px;
    box-shadow:0 .125rem .25rem rgba(0,0,0,.05)
}
.intel-panel h6{font-size:.75rem;text-transform:uppercase;color:#0d6efd}
.intel-card{
    background:#fff;border-radius:.5rem;padding:14px;
    box-shadow:0 .125rem .25rem rgba(0,0,0,.05)
}
#intel-map{width:100%;height:320px;border:0}
</style>
