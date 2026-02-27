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
use app\helpers\GlobalHelper;
use yii\data\ActiveDataProvider;

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
        $otp_message = "Your OTP is: {$otp}";
        // Send OTP to email
        try {
            Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setSubject('Your Login OTP')
                ->setTextBody($otp_message)
                ->send();
        } catch (\Throwable $e) {
            Yii::error(
                'OTP Email failed: ' . $e->getMessage(),
                __METHOD__
            );
        }

        //send otp to sms
        try {
            GlobalHelper::SendSMS($user->phone, $otp_message);
        } catch (\Throwable $e) {
            Yii::error(
                'OTP SMS failed: ' . $e->getMessage(),
                __METHOD__
            );
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

    /** Only Authenticated user should access the logs **/
    public function actionLogs()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Log::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10, 
            ],
        ]);

        return $this->render('logs', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLogView($id)
    {
        $log = Log::findOne($id);

        if (!$log) {
            throw new \yii\web\NotFoundHttpException('Log not found');
        }

        return $this->render('log-view', [
            'log' => $log,
        ]);
    }


}
