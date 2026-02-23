<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>

<div class="dashboard-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard
            </h1>
            <p class="text-muted small mb-0">a summary of received and analysed data</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark-subtle text-dark border p-2">
                <i class="bi bi-clock-history me-1"></i> Last Updated: <?= date('H:i:s') ?>
            </span>
        </div>
    </div>

    <!-- Intelligence Pulse Widgets -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Intercepts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_messages'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-envelope-paper fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Critical Threats</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['critical_threats'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Decryption Rate</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?= $stats['decryption_rate'] ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats['decryption_rate'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-unlock fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <!-- Recent Intercepts -->
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Intercepts</h6>
                    <a href="<?= Url::to(['message/index']) ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Intercepted At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMessages as $message): ?>
                                <tr>
                                    <td>#<?= $message->id ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            <?= ucfirst($message->file_type ?: 'Text') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = 'bg-secondary';
                                            if ($message->status == 'analyzed' || $message->status == 'decrypted') $statusClass = 'bg-success';
                                            if ($message->status == 'failed') $statusClass = 'bg-danger';
                                            if ($message->status == 'analyzing') $statusClass = 'bg-info';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= ucfirst($message->status) ?></span>
                                    </td>
                                    <td class="small"><?= $message->intercepted_at ?></td>
                                    <td>
                                        <a href="<?= Url::to(['message/view', 'id' => $message->id]) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
