<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Communication Network';
?>

<div class="visualization-network">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted small mb-0">Interactive visualization of entity relationships and communication flows</p>
        </div>
        <div class="btn-group shadow-sm">
            <button class="btn btn-white border" onclick="fitNetwork()" title="Fit View">
                <i class="bi bi-fullscreen"></i>
            </button>
            <button class="btn btn-white border" onclick="togglePhysics()" title="Toggle Physics">
                <i class="bi bi-pause-fill"></i>
            </button>
            <button class="btn btn-primary" onclick="loadNetwork()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0 position-relative">
                    <div id="network-graph" style="width: 100%; height: 650px; background: #f8f9fc;"></div>
                    <div id="network-loader" class="position-absolute top-50 start-50 translate-middle d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h6 class="fw-bold mb-0">Network Statistics</h6>
                </div>
                <div class="card-body">
                    <div id="network-stats">
                        <div class="placeholder-glow">
                            <span class="placeholder col-12 mb-2"></span>
                            <span class="placeholder col-12 mb-2"></span>
                            <span class="placeholder col-12 mb-2"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h6 class="fw-bold mb-0">Legend</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Risk Levels</small>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-danger rounded-circle p-1 me-2" style="width: 12px; height: 12px;"></span>
                            <span class="small">Critical (75-100)</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-warning rounded-circle p-1 me-2" style="width: 12px; height: 12px;"></span>
                            <span class="small">High (50-74)</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-info rounded-circle p-1 me-2" style="width: 12px; height: 12px;"></span>
                            <span class="small">Medium (25-49)</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success rounded-circle p-1 me-2" style="width: 12px; height: 12px;"></span>
                            <span class="small">Low (0-24)</span>
                        </div>
                    </div>
                    
                    <div>
                        <small class="text-muted d-block mb-2">Entity Types</small>
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-person-fill me-2 text-muted"></i>
                            <span class="small">Person (Circle)</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-laptop me-2 text-muted"></i>
                            <span class="small">Device (Square)</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people-fill me-2 text-muted"></i>
                            <span class="small">Group (Diamond)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vis.js Library -->
<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

<script>
let network = null;
let physicsEnabled = true;

function loadNetwork() {
    const loader = document.getElementById('network-loader');
    loader.classList.remove('d-none');
    
    // Use proper Yii2 URL generation
    const url = '<?= Url::to(['visualization/network']) ?>';
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        loader.classList.add('d-none');
        renderNetwork(data);
    })
    .catch(error => {
        loader.classList.add('d-none');
        console.error('Error loading network:', error);
    });
}

function renderNetwork(data) {
    const container = document.getElementById('network-graph');
    const networkData = {
        nodes: new vis.DataSet(data.nodes || []),
        edges: new vis.DataSet(data.edges || [])
    };

    const options = {
        nodes: {
            shape: 'dot',
            size: 25,
            font: { size: 12, face: 'Inter, system-ui' },
            borderWidth: 2,
            shadow: true
        },
        edges: {
            width: 2,
            color: { inherit: 'from' },
            arrows: { to: { enabled: true, scaleFactor: 0.5 } },
            smooth: { type: 'continuous' }
        },
        physics: {
            enabled: true,
            barnesHut: { gravitationalConstant: -2000, centralGravity: 0.3, springLength: 150 },
            stabilization: { iterations: 100 }
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
            navigationButtons: true
        }
    };

    network = new vis.Network(container, networkData, options);

    network.on('click', function(params) {
        if (params.nodes.length > 0) {
            const nodeId = params.nodes[0];
            window.location.href = '<?= Url::to(['entity/view']) ?>?id=' + nodeId;
        }
    });
}

function fitNetwork() {
    if (network) network.fit({ animation: true });
}

function togglePhysics() {
    if (network) {
        physicsEnabled = !physicsEnabled;
        network.setOptions({ physics: { enabled: physicsEnabled } });
    }
}

function loadNetworkStats() {
    const url = '<?= Url::to(['visualization/stats']) ?>';
    fetch(url)
    .then(response => response.json())
    .then(data => {
        const statsHtml = `
            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>Total Entities</span>
                    <span class="fw-bold">${data.total_entities || 0}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>High Risk</span>
                    <span class="fw-bold text-danger">${data.high_risk_entities || 0}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>Connections</span>
                    <span class="fw-bold">${data.total_links || 0}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>Density</span>
                    <span class="fw-bold">${data.network_density || 0}%</span>
                </div>
            </div>
        `;
        document.getElementById('network-stats').innerHTML = statsHtml;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadNetwork();
    loadNetworkStats();
});
</script>

<style>
    #network-graph {
        border-radius: 0.5rem;
    }
    .vis-network {
        outline: none;
    }
    .btn-white {
        background: #fff;
        color: #4e73df;
    }
    .btn-white:hover {
        background: #f8f9fc;
    }
</style>
