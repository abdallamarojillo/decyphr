<?php

namespace app\models;

use Yii;
use yii\base\Model;

class OtpForm extends Model
{
    public $otp;

    public function rules()
    {
        return [
            ['otp', 'required'],
            ['otp', 'string', 'length' => 6],
        ];
    }

    public function verify()
    {
        $userId = Yii::$app->session->get('mfa_user_id');
        if (!$userId) {
            return false;
        }

        $user = User::findOne($userId);

        if (!$user || !$user->validateOtp($this->otp)) {
            $this->addError('otp', 'Invalid or expired OTP.');
            return false;
        }

        $user->markOtpUsed();

        //update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save(false);

        Yii::$app->user->login($user, 3600 * 24); //only auto authenticate the user in one day
        Yii::$app->session->remove('mfa_user_id');

        //Log the event
        Log::log(
            'USER_LOGIN_SUCCESS',
            'User logged in successfully',
            LogType::AUTH,
            ['username' => $user->username]
        );

        return true;
    }
}

?>