<?php
namespace paw\user\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use paw\user\models\User;

class ManageForm extends User
{
    public $model;

    public $first_name;
    public $last_name;
    public $username;
    public $password;
    public $password_confirm;
    public $email;

    public function init()
    {
        parent::init();

        if (!$this->model || !$this->model instanceof \paw\user\models\User) {
            throw new InvalidConfigException("config 'model' is required");
        }

        $this->setAttributes([
            'first_name'    => $this->model->first_name,
            'last_name'     => $this->model->last_name,
            'username'      => $this->model->username,
            'email'         => $this->model->email,
        ]);
    }

    public function rules()
    {
        return [
            [['email', 'first_name'], 'required'],
            [['email'], 'email'],
            [['email'], 'unique', 'targetAttribute' => ['email'], 'targetClass' => User::class, 'when' => function ($model) {
                return $model->email != $this->model->email;
            }],
            [['password'], 'string', 'min' => 6, 'skipOnEmpty' => true],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'when' => function ($model) {
                return !empty($password);
            }],
            [['last_name'], 'string'],
            [['username'], 'safe'],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) return false;

        $model = $this->model;
        
        $model->setAttributes([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $model->password = $this->password;
        }

        if (!$model->save())
        {
            if (YII_DEBUG) throw new \Exception(print_r($model->errors, 1));
            return fasle;
        }

        return $model; 
    }
}