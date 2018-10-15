<?php
namespace app\models;
use yii\base\Model;
use app\models\User;
/**
 * Signup form
 */
class SignupForm extends Model
{
    public $nickname;
    public $email;
    public $password;
    public $locale;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['nickname', 'trim'],
            ['nickname', 'required'],
            [['nickname','locale'], 'string', 'min' => 2, 'max' => 255],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }
    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->nickname = $this->nickname;
        $user->email = $this->email;
        $user->locale = $this->locale;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}