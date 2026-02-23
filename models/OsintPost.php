<?php
namespace app\models;

use yii\db\ActiveRecord;

class OsintPost extends ActiveRecord
{
    public static function tableName()
    {
        return 'osint_posts';
    }

    public function rules()
    {
        return [
            [['keyword', 'platform', 'text', 'author'], 'required'],
            [['created_at'], 'safe'],
            [['engagement'], 'safe'], // JSON column for likes, shares, etc.
            [['url', 'post_id', 'location', 'video_url', 'cover'], 'string', 'max' => 255],
        ];
    }
}