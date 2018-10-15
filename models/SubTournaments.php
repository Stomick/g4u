<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 19.07.2018
 * Time: 17:06
 */

namespace app\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class SubTournaments extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tournaments_sub}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}