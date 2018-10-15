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

class GameOffer extends ActiveRecord
{
    public static function tableName()
    {
        return 'game_offer';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}