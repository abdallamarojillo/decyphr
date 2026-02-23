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

        Yii::$app->user->login($user, 3600 * 24 * 30);
        Yii::$app->session->remove('mfa_user_id');

        return true;
    }
}

?>