<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>
<div class="site-login">
    <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #f8f9fa;">
        <div class="card shadow-lg border-0" style="max-width: 700px; width: 100%;">
            <div class="row g-0">
                <!-- Logo / Image Section with Gradient -->
                <div class="col-md-4 d-flex align-items-center justify-content-center" 
                     style="background: linear-gradient(135deg, #002f87, #03E277);">
                    <img src="<?= Yii::$app->params['logo'] ?>" alt="CryptIntel Logo" class="img-fluid p-3">
                </div>

                <!-- Form Section -->
                <div class="col-md-8">
                    <div class="card-body p-5">
                        <h3 class="card-title mb-2 text-center fw-bold"><?= Yii::$app->name ?? '';?></h3>
                        <p class="text-center text-muted mb-4"><?= Yii::$app->params['app_description'] ?></p>

                        <?php $form = ActiveForm::begin([
                            'id' => 'login-form',
                            'layout' => 'horizontal',
                            'fieldConfig' => [
                                'template' => "{label}\n{input}\n{error}",
                                'labelOptions' => ['class' => 'form-label'],
                                'inputOptions' => ['class' => 'form-control'],
                                'errorOptions' => ['class' => 'invalid-feedback'],
                            ],
                        ]); ?>

                        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Username']) ?>
                        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']) ?>
                        <?= $form->field($model, 'rememberMe')->checkbox([
                            'template' => "<div class=\"form-check mb-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
                        ]) ?>

                        <div class="d-grid mt-4">
                            <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-lg', 'name' => 'login-button']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
