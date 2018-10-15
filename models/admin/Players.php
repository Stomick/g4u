<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 23.07.2018
 * Time: 16:29
 */
namespace app\models\admin;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Players extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%players}}';
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