<?php
use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'System Logs';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fas fa-history text-primary me-2"></i>System Logs
            </h4>
            <p class="text-muted small mb-0">Monitor and audit system activities and events.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0"> <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n<div class='card-footer d-flex justify-content-between align-items-center bg-white py-3'>{summary}{pager}</div>",
                'tableOptions' => [
                    'class' => 'table table-hover mb-0 align-middle',
                ],
                // Customizing the pager to look modern
                'pager' => [
                    'class' => \yii\bootstrap5\LinkPager::class,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold px-4'],
                        'contentOptions' => ['class' => 'px-4 text-muted'],
                    ],
                    [
                        'attribute' => 'action',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold'],
                        'contentOptions' => ['class' => 'fw-bold text-dark'],
                    ],
                    [
                        'attribute' => 'log_type',
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold'],
                        'value' => function($model) {
                            // Dynamic color mapping
                            $class = match($model->log_type) {
                                'Error' => 'bg-danger-subtle text-danger',
                                'Warning' => 'bg-warning-subtle text-warning-emphasis',
                                'Login' => 'bg-info-subtle text-info-emphasis',
                                default => 'bg-secondary-subtle text-secondary-emphasis',
                            };
                            return "<span class='badge rounded-pill {$class} px-3'>".Html::encode($model->log_type)."</span>";
                        },
                    ],
                    [
                        'label' => 'User',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold'],
                        'format' => 'raw',
                        'value' => function($model) {
                            $user = $model->user_id !== null ? $model->user_id : 'System';
                            $icon = $model->user_id ? 'fa-user' : 'fa-robot';
                            return "<div class='d-flex align-items-center'>
                                        <div class='avatar-xs me-2 d-flex align-items-center justify-content-center bg-light rounded-circle' style='width: 30px; height: 30px;'>
                                            <i class='fas {$icon} text-muted' style='font-size: 0.8rem;'></i>
                                        </div>
                                        <span>{$user}</span>
                                    </div>";
                        },
                    ],
                    [
                        'attribute' => 'ip_address',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold'],
                        'contentOptions' => ['class' => 'font-monospace small'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold'],
                        'format' => ['datetime'],
                        'contentOptions' => ['class' => 'text-muted small'],
                    ],
                    [
                        'header' => 'Actions',
                        'headerOptions' => ['class' => 'bg-light text-muted small fw-bold text-end px-4'],
                        'contentOptions' => ['class' => 'text-end px-4'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(
                                '<i class="fas fa-eye me-1"></i> Details',
                                ['site/log-view', 'id' => $model->id],
                                ['class' => 'btn btn-sm btn-light border fw-medium']
                            );
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php
$this->registerCssFile('https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css');
$this->registerJsFile(
    'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJs("
    $('.table').DataTable();
", \yii\web\View::POS_READY);