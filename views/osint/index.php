<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'OSINT Dashboard';
?>

<div class="osint-index container py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <h1 class="font-weight-bold mb-1">
                <i class="fas fa-shield-alt text-primary mr-2"></i> OSINT Dashboard
            </h1>
            <p class="text-muted small mb-0">Open-Source Intelligence Analysis - Twitter (X), Tiktok, Facebook</p>
        </div>
    </div>

    <div class="intelligence-grid mb-4">
        <h6 class="section-label">Counter-Terrorism & Violent Crimes</h6>
        <div class="d-flex flex-wrap mb-3">
            <button class="tactical-btn" data-keyword="terrorism bomb attack explosive ISIS Al-Shabaab IED jihad recruit">
                <i class="fas fa-bomb text-danger"></i> Terrorism
            </button>
            <button class="tactical-btn" data-keyword="kidnapped abducted ransom hostage missing captured">
                <i class="fas fa-user-secret text-danger"></i> Kidnapping
            </button>
            <button class="tactical-btn" data-keyword="gang shooting cartel murder drive-by">
                <i class="fas fa-skull-crossbones text-danger"></i> Gang Activity
            </button>
            <button class="tactical-btn" data-keyword="tribal conflict ethnic clash raid cattle rustling">
                <i class="fas fa-khanda text-danger"></i> Tribal Conflicts
            </button>
        </div>
    </div>

    <div class="search-container mb-4">
        <form id="osint-search-form">
            <div class="input-group shadow-sm">
                <input type="text" id="keyword-input" class="form-control" placeholder="manual scan...">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Scan</button>
                </div>
            </div>
        </form>
    </div>

    <div id="loading-spinner" style="display:none" class="text-center py-5">
        <div class="intel-loader"></div>
        <h6 class="text-muted mt-3">Analyzing global streams…</h6>
    </div>

    <div id="results-container" style="display:none">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white font-weight-bold">
                <i class="fas fa-brain mr-2"></i> Strategic Brief
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9 border-right">
                        <h6 class="text-muted">Threat Summary</h6>
                        <p id="ai-summary-text" class="small mb-0"></p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6 class="text-muted">Threat Level</h6>
                        <div id="threat-score" class="display-4 font-weight-bold">0</div>
                        <div id="threat-level-badge" class="badge p-2 badge-success">LOW</div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="section-label">Geographic Hotspots</h6>
        <iframe
            id="intel-map"
            class="mb-4"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.openstreetmap.org/export/embed.html?bbox=33.5,-4.9,41.9,5.3&layer=mapnik&zoom=6">
        </iframe>

        <h6 class="section-label">Live Intel Feed</h6>
        <div class="row" id="hotspot-list"></div>
    </div>
</div>

<script>
document.querySelectorAll('.tactical-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('keyword-input').value = this.getAttribute('data-keyword');
        document.getElementById('osint-search-form').dispatchEvent(new Event('submit'));
    });
});

document.getElementById('osint-search-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const keyword = document.getElementById('keyword-input').value;

    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('results-container').style.display = 'none';

    fetch('<?= Url::to(['osint/fetch']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'
        },
        body: 'keyword=' + encodeURIComponent(keyword)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading-spinner').style.display = 'none';
        if (!data.success) return;

        const osint = data.data;
        const report = osint.ai_report || {};

        let summary = report.threat_summary;
        if (typeof summary === 'object' && summary !== null) {
            summary = Object.values(summary).join(' ');
        }

        document.getElementById('ai-summary-text').textContent =
            summary || 'No strategic summary available.';

        const score = Math.round(osint.threat_score || 0);
        document.getElementById('threat-score').textContent = score;

        const badge = document.getElementById('threat-level-badge');
        let level = 'LOW', cls = 'badge-success';
        if (score > 70) { level = 'CRITICAL'; cls = 'badge-danger'; }
        else if (score > 40) { level = 'ELEVATED'; cls = 'badge-warning'; }

        badge.textContent = level;
        badge.className = `badge p-2 ${cls}`;

        const list = document.getElementById('hotspot-list');
        list.innerHTML = '';

        (osint.platforms?.x?.data || []).slice(0, 6).forEach(post => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-3';
            col.innerHTML = `
                <div class="intel-card">
                    <div class="intel-header">@${post.author}</div>
                    <div class="intel-meta">${post.created_at} • ${post.location}</div>
                    <div class="intel-text">${post.text}</div>
                    <div class="intel-stats">
                        ❤ ${post.engagement.likes} • 🔁 ${post.engagement.shares} • 👁 ${post.engagement.views}
                    </div>
                </div>
            `;
            list.appendChild(col);
        });

        // --- KEYLESS KENYA MAP UPDATE ---
        const locations = (osint.platforms?.x?.data || [])
            .map(p => p.location)
            .filter(l => l && l.length > 2);

        if (locations.length) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locations[0] + ', Kenya')}`)
                .then(r => r.json())
                .then(res => {
                    if (!res[0]) return;

                    const lat = parseFloat(res[0].lat);
                    const lon = parseFloat(res[0].lon);

                    const d = 0.25;
                    const bbox = [
                        lon - d, lat - d,
                        lon + d, lat + d
                    ].join(',');

                    document.getElementById('intel-map').src =
                        `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&zoom=9&marker=${lat},${lon}`;
                });
        }

        document.getElementById('results-container').style.display = 'block';
    });
});
</script>

<style>
.section-label{color:#0d6efd;font-size:.75rem;letter-spacing:1px;text-transform:uppercase;margin:20px 0 12px;border-left:3px solid #0d6efd;padding-left:10px}
.tactical-btn{background:#fff;color:#212529;border:1px solid #dee2e6;padding:8px 14px;margin:4px;border-radius:.25rem;font-size:.8rem;transition:.2s;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 .125rem .25rem rgba(0,0,0,.05)}
.tactical-btn:hover{background:#f8f9fa;transform:translateY(-1px);box-shadow:0 .25rem .5rem rgba(0,0,0,.1)}
.system-status{color:#198754;font-size:.75rem;border:1px solid #d1e7dd;padding:4px 12px;border-radius:20px;background:#f8fffb}
.pulse{height:8px;width:8px;background:#198754;border-radius:50%;display:inline-block;margin-right:5px;animation:pulse 2s infinite}
@keyframes pulse{0%{opacity:1}50%{opacity:.3}100%{opacity:1}}
.intel-loader{width:40px;height:40px;border:4px solid #e9ecef;border-top:4px solid #0d6efd;border-radius:50%;animation:spin 1s linear infinite;margin:auto}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
.intel-card{background:#fff;border:1px solid #e9ecef;border-radius:.5rem;padding:14px;height:100%;transition:.2s;box-shadow:0 .125rem .25rem rgba(0,0,0,.05)}
.intel-card:hover{box-shadow:0 .5rem 1rem rgba(0,0,0,.15)}
.intel-header{color:#0d6efd;font-weight:600;font-size:.85rem}
.intel-meta{color:#6c757d;font-size:.7rem;margin-bottom:6px}
.intel-text{font-size:.8rem;line-height:1.4;margin-bottom:8px;color:#212529}
.intel-stats{font-size:.7rem;color:#6c757d;border-top:1px solid #f1f3f5;padding-top:6px}
#intel-map{height:320px;width:100%;border-radius:.5rem;border:1px solid #e9ecef;box-shadow:0 .125rem .25rem rgba(0,0,0,.05)}
</style>
