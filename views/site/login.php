<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>

<div class="site-login bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="row g-0">
                        
                        <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center p-5" 
                             style="background: linear-gradient(135deg, #002f87 0%, #03E277 100%);">
                            <div class="text-center text-white">
                                <img src="<?= Yii::$app->params['logo'] ?>" alt="Logo" class="img-fluid mb-4 w-50">
                                <h2 class="fw-bold">Welcome Back</h2>
                                <p class="opacity-75 small">Secure access to your crypto intelligence dashboard.</p>
                            </div>
                        </div>

                        <div class="col-md-7 bg-white p-4 p-lg-5">
                            <div class="mb-5">
                                <h3 class="fw-bold text-dark mb-1"><?= Yii::$app->name ?></h3>
                                <p class="text-muted small"><?= Yii::$app->params['app_description'] ?? 'Sign in to continue' ?></p>
                            </div>

                            <?php $form = ActiveForm::begin([
                                'id' => 'login-form',
                                'layout' => 'default', // Changed to default for a cleaner stacked look
                                'fieldConfig' => [
                                    'template' => "<div class='mb-3'>{label}\n{input}\n{error}</div>",
                                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                                    'inputOptions' => ['class' => 'form-control form-control-lg bg-light border-0 shadow-none'],
                                    'errorOptions' => ['class' => 'invalid-feedback'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'username')->textInput([
                                'autofocus' => true, 
                                'placeholder' => 'Enter your username'
                            ]) ?>

                            <?= $form->field($model, 'password')->passwordInput([
                                'placeholder' => 'Enter your password'
                            ]) ?>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <?= $form->field($model, 'rememberMe')->checkbox([
                                    'class' => 'form-check-input mt-0',
                                    'template' => "<div class=\"form-check\">{input} {label}</div>",
                                ])->label('Remember me', ['class' => 'form-check-label small text-muted ms-2']) ?>
                                
                            </div>

                            <div class="d-grid gap-2">
                                <?= Html::submitButton('Sign In', [
                                    'class' => 'btn btn-primary btn-lg border-0 rounded-3 fw-bold shadow-sm', 
                                    'name' => 'login-button',
                                    'style' => 'background-color: #002f87;' // Only using style for your specific brand color
                                ]) ?>
                            </div>

                            <?php ActiveForm::end(); ?>

                            <div class="mt-5 text-center">
                                <span class="text-muted small">Don't have an account?</span>
                                <a href="#" class="text-primary small fw-bold text-decoration-none ms-1">Contact Admin</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>