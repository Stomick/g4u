<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 19.07.2018
 * Time: 17:06
 */

namespace app\models;

/**
 * @property $player_id integer
 */
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Commands extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commands}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function getPts($commId , $tourId){

    }

    public function getGoals($comId, $tourId){

    }
}


