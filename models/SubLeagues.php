<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 23.07.2018
 * Time: 16:29
 */
namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class SubLeagues extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%leagues_sub}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}