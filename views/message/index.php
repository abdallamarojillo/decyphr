<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Intelligence Messages';
?>

<div class="message-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-envelope-paper-fill text-primary me-2"></i><?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted small mb-0">Manage and analyze intercepted communications</p>
        </div>
        <a href="<?= Url::to(['message/upload']) ?>" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> New Analysis
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    // Removed filterModel to fix "Undefined variable $searchModel"
                    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                    'headerRowOptions' => ['class' => 'table-light border-bottom'],
                    'summaryOptions' => ['class' => 'p-3 text-muted small'],
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'fw-bold text-muted'],
                        ],
                        [
                            'attribute' => 'file_type',
                            'label' => 'Type',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'value' => function ($model) {
                                $icon = 'bi-file-text';
                                $color = 'secondary';
                                
                                switch ($model->file_type) {
                                    case 'image':
                                        $icon = 'bi-image';
                                        $color = 'info';
                                        break;
                                    case 'audio':
                                        $icon = 'bi-mic';
                                        $color = 'warning';
                                        break;
                                }
                                
                                return "<span class='badge bg-{$color}-subtle text-{$color} border border-{$color}-subtle px-2 py-1'>
                                            <i class='bi {$icon} me-1'></i>" . ucfirst($model->file_type ?: 'text') . "
                                        </span>";
                            },
                        ],
                        [
                            'attribute' => 'encrypted_content',
                            'label' => 'Content Preview',
                            'value' => function ($model) {
                                $content = $model->encrypted_content;
                                if (empty($content) && $model->file_path) {
                                    return "Binary File (" . basename($model->file_path) . ")";
                                }
                                return mb_strimwidth($content, 0, 60, "...");
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'value' => function ($model) {
                                $class = 'bg-secondary';
                                $icon = 'bi-clock';
                                
                                switch ($model->status) {
                                    case 'analyzed':
                                    case 'decrypted':
                                        $class = 'bg-success';
                                        $icon = 'bi-check-circle';
                                        break;
                                    case 'analyzing':
                                        $class = 'bg-primary';
                                        $icon = 'bi-gear-wide-connected spin';
                                        break;
                                    case 'failed':
                                        $class = 'bg-danger';
                                        $icon = 'bi-exclamation-triangle';
                                        break;
                                }
                                
                                return "<div class='badge {$class} rounded-pill px-3 py-2'>
                                            <i class='bi {$icon} me-1'></i>" . ucfirst($model->status) . "
                                        </div>";
                            },
                        ],
                        [
                            'attribute' => 'intercepted_at',
                            'label' => 'Intercepted',
                            'format' => ['datetime', 'php:M d, H:i'],
                            'headerOptions' => ['style' => 'width: 160px;'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Actions',
                            'headerOptions' => ['style' => 'width: 120px;', 'class' => 'text-center'],
                            'contentOptions' => ['class' => 'text-center'],
                            'template' => '{view} {delete}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="bi bi-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-primary border-0',
                                        'title' => 'View Intelligence',
                                    ]);
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<i class="bi bi-trash"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-danger border-0',
                                        'title' => 'Delete',
                                        'data-confirm' => 'Are you sure you want to delete this intelligence record?',
                                        'data-method' => 'post',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<style>
    .message-index .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #6c757d;
        padding: 1rem;
    }
    .message-index .table tbody td {
        padding: 1rem;
        font-size: 0.9rem;
    }
    .message-index .badge {
        font-weight: 500;
        letter-spacing: 0.02em;
    }
    .message-index .bg-info-subtle { background-color: #e1f5fe !important; }
    .message-index .text-info { color: #0288d1 !important; }
    .message-index .bg-warning-subtle { background-color: #fff8e1 !important; }
    .message-index .text-warning { color: #f57c00 !important; }
    .message-index .bg-secondary-subtle { background-color: #f5f5f5 !important; }
    .message-index .text-secondary { color: #616161 !important; }
    
    .spin {
        animation: spin 2s linear infinite;
        display: inline-block;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .message-index .btn-outline-primary:hover, 
    .message-index .btn-outline-danger:hover {
        background-color: rgba(0,0,0,0.05);
    }
</style>
