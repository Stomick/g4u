<?php
namespace app\models;

use Yii;
/**
 * This is the model class for table "tokens".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $expire_time
 *
 * @property User $id0
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tokens';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id'], 'integer'],
            [['expire_time'], 'safe'],
            [['token'], 'string', 'max' => 255],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'expire_time' => 'Time',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(User::className(), ['id' => 'id']);
    }
    /**
     * @inheritdoc
     * @return TokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TokenQuery(get_called_class());
    }
}