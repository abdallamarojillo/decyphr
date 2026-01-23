<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - <?= Yii::$app->name ?? '' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/custom.css">

    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background: linear-gradient(135deg, #002f87, #03E277);">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= Url::to(['/']) ?>">
                <img src="<?= Yii::$app->params['logo'] ?>" alt="<?= Yii::$app->name ?>" height="30">
                <?= Yii::$app->name; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['dashboard/index']) ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['message/index']) ?>">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['message/upload']) ?>">
                            <i class="fas fa-upload"></i> Upload Message
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                        . Html::submitButton(
                            '<i class="fas fa-sign-out-alt"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-outline-light btn-sm mt-1']
                        )
                        . Html::endForm() ?>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['site/login']) ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.js"></script>
    <!-- Vis.js -->
    <script src="https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.js"></script>
    <link href="https://unpkg.com/vis-network@9.1.6/dist/dist/vis-network.min.css" rel="stylesheet">
    <!-- Custom JS -->
    <script src="/js/cryptanalysis.js"></script>

    <script>
    // Tactical Dark Mode Toggle
    const toggleBtn = document.getElementById('dark-mode-toggle');
    const body = document.body;

    if (localStorage.getItem('dark-mode') === 'enabled') {
        body.classList.add('dark-mode');
        if (toggleBtn) toggleBtn.innerHTML = '<i class="bi bi-sun me-1"></i> Light Mode';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('dark-mode', 'enabled');
                toggleBtn.innerHTML = '<i class="bi bi-sun me-1"></i> Light Mode';
            } else {
                localStorage.setItem('dark-mode', 'disabled');
                toggleBtn.innerHTML = '<i class="bi bi-moon-stars me-1"></i> Tactical Mode';
            }
        });
    }
    </script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>