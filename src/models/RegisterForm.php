<?php
namespace paw\user\models;

use paw\user\models\User;
use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $first_name;
    public $last_name;
    public $username;
    public $password;
    public $password_confirm;
    public $email;
    public $agree;

    public $roles = [];

    public $emailVerification = true;

    public $enableAgree = false;

    public function rules()
    {
        return [
            [['username', 'password', 'password_confirm', 'email', 'first_name'], 'required'],
            [['username'], 'string', 'min' => 3],
            [['username'], 'unique', 'targetAttribute' => ['username'], 'targetClass' => User::class],
            [['email'], 'email'],
            [['email'], 'unique', 'targetAttribute' => ['email'], 'targetClass' => User::class],
            [['password'], 'string', 'min' => 6],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password'],
            [['last_name'], 'string'],
            [['agree'], 'boolean'],
            [['agree'], 'required', 'requiredValue' => '1', 'when' => function ($model) {
                return $this->enableAgree;
            }],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return false;
        }

        $model = new User([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
        ]);

        if (!$model->save()) {
            if (YII_DEBUG) {
                throw new \Exception(print_r($model->errors, 1));
            }

            return fasle;
        }

        // roles
        $auth = Yii::$app->authManager;
        foreach ($this->roles as $roleName) {
            $role = $auth->getRole($roleName);
            $auth->assign($role, $model->id);
        }

        return $model;
    }
}
