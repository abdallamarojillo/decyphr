<?php
namespace app\models;

use yii\db\ActiveRecord;

class OsintAiAnalysis extends ActiveRecord
{
    public static function tableName()
    {
        return 'osint_ai_analysis';
    }

    public function rules()
    {
        return [
            [['keyword', 'summary', 'numerical_score'], 'required'],
            [['report'], 'safe'], // JSON full AI output
            [['analyzed_at'], 'safe'],
        ];
    }
}