<?php

namespace app\models;

use app\helpers\GlobalHelper;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Send an otp when a user supplies the correct username and password.
     * @return bool whether the user is successfully authenticated
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            // Generate OTP
            $otp = $user->GenerateOtp();
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

        // Send OTP to phone
        try {
            GlobalHelper::SendSMS($user->phone, $otp_message);
        } catch (\Throwable $e) {
            Yii::error(
                'OTP SMS failed: ' . $e->getMessage(),
                __METHOD__
            );
        }

            Yii::$app->session->set('mfa_user_id', $user->id);

            return true;
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
