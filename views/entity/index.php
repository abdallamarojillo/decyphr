<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Entities';
?>

<div class="entity-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> Entities</h1>
        <a href="/entities/create" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Entity
        </a>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            'id',
            'entity_code',
            [
                'attribute' => 'name',
                'format' => 'text',
                'value' => function($model) {
                    return $model->name ?: 'N/A';
                }
            ],
            [
                'attribute' => 'entity_type',
                'format' => 'html',
                'value' => function($model) {
                    $icons = [
                        'person' => 'fa-user',
                        'group' => 'fa-users',
                        'device' => 'fa-mobile-alt',
                        'unknown' => 'fa-question-circle'
                    ];
                    $icon = $icons[$model->entity_type] ?? 'fa-question';
                    return '<i class="fas ' . $icon . '"></i> ' . ucfirst($model->entity_type);
                }
            ],
            [
                'attribute' => 'risk_score',
                'format' => 'html',
                'value' => function($model) {
                    return '<div class="progress" style="height: 25px; min-width: 100px;">
                        <div class="progress-bar bg-' . $model->getRiskClass() . '" 
                             role="progressbar" 
                             style="width: ' . $model->risk_score . '%">
                            ' . $model->risk_score . '
                        </div>
                    </div>';
                }
            ],
            [
                'attribute' => 'last_seen',
                'format' => 'datetime',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="fas fa-eye"></i>', ['/entities/' . $model->id], [
                            'class' => 'btn btn-sm btn-primary',
                            'title' => 'View'
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
