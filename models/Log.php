<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Log extends ActiveRecord
{
    public static function tableName()
    {
        return 'logs';
    }

    public function rules()
    {
        return [
            [['action', 'action_description', 'ip_address', 'log_type', 'url', 'http_method'], 'required'],
            [['associated_data'], 'string'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['action'], 'string', 'max' => 1000],
            [['action_description'], 'string', 'max' => 5000],
            [['ip_address'], 'string', 'max' => 60],
            [['log_type'], 'string', 'max' => 100],
            [['url', 'user_agent'], 'string', 'max' => 500],
            [['http_method'], 'string', 'max' => 10],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s.u');
        }
        return parent::beforeSave($insert);
    }

    /**
     * Central logging helper
     */
    public static function log($action, $description, $type, $data = null)
    {
        try {
            $log = new self();
            $log->action = $action;
            $log->action_description = $description;
            $log->log_type = $type;
            $log->associated_data = is_array($data) ? json_encode($data) : $data;
            $log->ip_address = Yii::$app->request->userIP ?? 'CLI';
            $log->url = Yii::$app->request->absoluteUrl ?? 'CLI';
            $log->http_method = Yii::$app->request->method ?? 'CLI';
            $log->user_agent = Yii::$app->request->userAgent ?? null;
            $log->user_id = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
            $log->save(false);
        } catch (\Throwable $e) {
            // Never block app execution because of logging
        }
    }
}