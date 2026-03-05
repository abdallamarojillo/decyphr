<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AppAsset;
AppAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - <?= Yii::$app->name ?? '' ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= Url::to(['/']) ?>">
            <img src="<?= Yii::$app->params['logo'] ?>" alt="<?= Yii::$app->name ?>" height="35" class="me-2">
            <span class="fw-bold tracking-tight text-dark"><?= Yii::$app->name; ?></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li class="nav-item me-2">
                        <a class="nav-link fw-medium px-3 text-secondary" href="<?= Url::to(['dashboard/index']) ?>">
                            <i class="fas fa-tachometer-alt me-1 opacity-75"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link fw-medium px-3 text-secondary" href="<?= Url::to(['message/index']) ?>">
                            <i class="fas fa-envelope me-1 opacity-75"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link fw-medium px-3 text-secondary" href="<?= Url::to(['osint/index']) ?>">
                            <i class="fab fa-connectdevelop me-1 opacity-75"></i> OSINT
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link fw-medium px-3 text-secondary" href="<?= Url::to(['site/logs']) ?>">
                            <i class="fa fa-history me-1 opacity-75"></i> Logs
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-center">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-primary px-3" href="<?= Url::to(['message/upload']) ?>">
                            <i class="fas fa-cloud-upload-alt me-1"></i> Upload
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                        . Html::submitButton(
                            '<i class="fas fa-sign-out-alt me-1"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold']
                        )
                        . Html::endForm() ?>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" href="<?= Url::to(['site/login']) ?>" 
                           style="background-color: #002f87; border: none;">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

    <div class="container-fluid mt-4">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted"><?= Yii::$app->name; ?> &copy; <?= date('Y') ?> -
                <?= Yii::$app->params['app_description'] ?></span>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>