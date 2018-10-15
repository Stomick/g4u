<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * usernameForm is the model behind the username form.
 */
class LoginForm extends Model
{
    public $email;
    public $password;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email and password are both required
            [['email', 'password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            // rememberMe must be a boolean value
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect email or password.');
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        $user = $this->getUser();
        if ($this->validate()) {

            return $user;

        }
        return false;
    }

    public function adminlogin()
    {
        $user = $this->getUser();
        if ($this->validate()) {
            if ($user->type == 'admin' || $user->type == 'superadmin') {
                return $user;
            } else {
                return ['error' => true, 'message' => 'You not have premission'];
            }
        }
        return false;
    }

    public function email()
    {
        if ($this->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[email]]
     *
     * @return User|null
     */
    private function getUser()
    {

        if ($this->_user === false) {
            $this->_user = User::findByemail($this->email);

        }
        return $this->_user;
    }
}
