<?php
namespace paw\user\models;

use Yii;
use yii\base\Model;
use paw\user\models\User;

class LoginForm extends Model
{
    public $username;
    public $password;

    public $access = [];

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['password'], 'validatePassword', 'skipOnError' => true],
            [['password'], 'validateAccess', 'skipOnError' => true],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) 
        {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            }
        }
    }

    public function validateAccess($attribute, $params)
    {
        if (!$this->hasErrors() && $this->access) 
        {
            $user = $this->getUser();
            if ($user) 
            {
                $hasAccess = false;
                foreach ($this->access as $access) {
                    if ($user->can($access)) {
                        $hasAccess = true;
                        break;
                    }
                }
                
                if (!$hasAccess) 
                {
                    if (YII_DEBUG) {
                        $this->addError($attribute, Yii::t('app', 'No access'));
                    } else {
                        $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
                    }
                }
            }
        }
    }

    protected function getUser()
    {
        return User::findByLogin($this->username);
    }

    public function submit()
    {
        if (!$this->validate()) return false;
        return $this->getUser();
    }
}