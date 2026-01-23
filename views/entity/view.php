<?php

use yii\helpers\Html;

$this->title = 'Entity: ' . ($entity->name ?: $entity->entity_code);
?>

<div class="entity-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user"></i> <?= Html::encode($entity->name ?: $entity->entity_code) ?></h1>
        <a href="/entities" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Entity Details -->
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-<?= $entity->getRiskClass() ?> text-white">
                    <h5><i class="fas fa-info-circle"></i> Entity Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Entity Code:</th>
                            <td><code><?= Html::encode($entity->entity_code) ?></code></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><?= ucfirst($entity->entity_type) ?></td>
                        </tr>
                        <tr>
                            <th>Risk Score:</th>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-<?= $entity->getRiskClass() ?>" 
                                         role="progressbar" 
                                         style="width: <?= $entity->risk_score ?>%">
                                        <?= $entity->risk_score ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= $entity->getRiskLevel() ?> Risk</small>
                            </td>
                        </tr>
                        <tr>
                            <th>First Seen:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($entity->first_seen)) ?></td>
                        </tr>
                        <tr>
                            <th>Last Seen:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($entity->last_seen)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-link"></i> Connections</h5>
                </div>
                <div class="card-body">
                    <p><strong>Outgoing Links:</strong> <?= count($outgoingLinks) ?></p>
                    <p><strong>Incoming Links:</strong> <?= count($incomingLinks) ?></p>
                    <a href="/visualization/network?entities=<?= $entity->id ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-project-diagram"></i> View Network
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Activity Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Activity Timeline</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($timeline)): ?>
                        <div class="timeline">
                            <?php foreach ($timeline as $event): ?>
                            <div class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <?php if ($event['type'] == 'sent'): ?>
                                            <i class="fas fa-arrow-right text-primary"></i>
                                            <strong>Sent to:</strong> <?= Html::encode($event['destination']) ?>
                                        <?php else: ?>
                                            <i class="fas fa-arrow-left text-success"></i>
                                            <strong>Received from:</strong> <?= Html::encode($event['source']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= date('Y-m-d H:i', strtotime($event['date'])) ?></small>
                                </div>
                                <div class="mt-1">
                                    <code class="small"><?= Html::encode($event['content']) ?></code>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No activity recorded</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Communication Links -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-network-wired"></i> Communication Links</h5>
                </div>
                <div class="card-body">
                    <h6>Outgoing Communications</h6>
                    <?php if (!empty($outgoingLinks)): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Target</th>
                                <th>Messages</th>
                                <th>Link Strength</th>
                                <th>Last Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($outgoingLinks as $link): ?>
                            <tr>
                                <td>
                                    <a href="/entities/<?= $link->target_entity_id ?>">
                                        <?= Html::encode($link->targetEntity->name ?: $link->targetEntity->entity_code) ?>
                                    </a>
                                </td>
                                <td><?= $link->message_count ?></td>
                                <td>
                                    <div class="progress" style="height: 20px; width: 100px;">
                                        <div class="progress-bar" style="width: <?= $link->link_strength ?>%">
                                            <?= $link->link_strength ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($link->last_contact)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">No outgoing links</p>
                    <?php endif; ?>

                    <h6 class="mt-4">Incoming Communications</h6>
                    <?php if (!empty($incomingLinks)): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Messages</th>
                                <th>Link Strength</th>
                                <th>Last Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incomingLinks as $link): ?>
                            <tr>
                                <td>
                                    <a href="/entities/<?= $link->source_entity_id ?>">
                                        <?= Html::encode($link->sourceEntity->name ?: $link->sourceEntity->entity_code) ?>
                                    </a>
                                </td>
                                <td><?= $link->message_count ?></td>
                                <td>
                                    <div class="progress" style="height: 20px; width: 100px;">
                                        <div class="progress-bar" style="width: <?= $link->link_strength ?>%">
                                            <?= $link->link_strength ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($link->last_contact)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">No incoming links</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
