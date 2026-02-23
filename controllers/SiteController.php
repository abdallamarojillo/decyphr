<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\OtpForm;
use app\models\User;
use app\models\Log;
use app\models\LogType;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/verify-otp']);
        }

        return $this->render('login', ['model' => $model]);
    }

    public function actionVerifyOtp()
    {
        $model = new OtpForm();

        if ($model->load(Yii::$app->request->post()) && $model->verify()) {
            return $this->goHome();
        }

        return $this->render('verify-otp', ['model' => $model]);
    }

    public function actionResendOtp()
    {
        $userId = Yii::$app->session->get('mfa_user_id');
        if (!$userId) {
            return $this->redirect(['site/login']);
        }

        $user = User::findOne($userId);
        $otp = $user->generateOtp();

        Yii::$app->mailer->compose()
            ->setTo($user->email)
            ->setSubject('Your New OTP')
            ->setTextBody("Your OTP is: {$otp}")
            ->send();

        if (!empty($user->phone)) {
            Yii::$app->sms->send($user->phone, "Your OTP is {$otp}");
        }

        Yii::$app->session->setFlash('success', 'A new OTP has been sent.');

        return $this->redirect(['site/verify-otp']);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        $username = Yii::$app->user->identity->username; // store username before logout
        Yii::$app->user->logout();

        // Log the event
        Log::log(
            'USER_LOGOUT_SUCCESS',
            'User logged out successfully',
            LogType::AUTH,
            ['username' => $username]
        );

        return $this->goHome();
    }

    /**
     * Index action redirects to dashboard or login
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        return $this->redirect(['dashboard/index']);
    }
}
