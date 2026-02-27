<?php
namespace app\helpers;

use Yii;

class GlobalHelper
{
    public static function formatPhoneNumber($phone)
    {
        // Remove spaces and trim
        $phone = trim(str_replace(' ', '', $phone));

        // 07XXXXXXXX translates to +2547XXXXXXXX
        if (substr($phone, 0, 2) === "07") {
            return "+2547" . substr($phone, 2);
        }

        // 01XXXXXXXX translates to +2541XXXXXXXX
        if (substr($phone, 0, 2) === "01") {
            return "+2541" . substr($phone, 2);
        }

        // Already formatted
        if (substr($phone, 0, 4) === "+254") {
            return $phone;
        }

        return $phone;
    }

    public static function SendSMS($mobile, $message)
    {
        $mobile = self::formatPhoneNumber($mobile);

        $url = 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

        $headers = [
            'Content-type: application/json',
            'AccessKey: GCyrv63CaaJNAgoYRuM2xTGw1hL2dKQt',
        ];

        $payload = [
            "SenderId" => Yii::$app->params['sms_api_sender_id'],
            "IsUnicode" => true,
            "IsFlash" => true,
            "MessageParameters" => [
                [
                    "Number" => $mobile,
                    "Text" => $message
                ]
            ],
            "ApiKey" => Yii::$app->params['sms_api_api_key'],
            "ClientId" => Yii::$app->params['sms_api_client_id']
        ];

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        print_r($response);

        return $response;
    }

    public static function CurrentUser($key = null)
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        $user = Yii::$app->user->identity;

        $data = [
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->role ?? 'na',
        ];

        // Return full array or a single value
        return $key === null ? $data : ($data[$key] ?? null);
    }
}