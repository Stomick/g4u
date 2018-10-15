<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 19.07.2018
 * Time: 17:06
 */

namespace app\models\statistic;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class PersonalToGames extends ActiveRecord
{
    public static function tableName()
    {
        return 'assignments';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}