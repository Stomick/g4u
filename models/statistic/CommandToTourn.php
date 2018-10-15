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

class CommandToTourn extends ActiveRecord
{
    public static function tableName()
    {
        return 'cm_to_tm';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}