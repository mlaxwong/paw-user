<?php
namespace paw\user\models;

use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use paw\base\ExpirableInterface;
use paw\behaviors\IpBehavior;
use paw\behaviors\TokenBehavior;
use paw\user\models\User;

class ForgetPasswordVerification extends ActiveRecord implements ExpirableInterface
{
    public function behaviors()
    {
        return [
            BlameableBehavior::class,
            IpBehavior::class,
            [
                'class' => TokenBehavior::class,
                'data' => [
                    'email',
                ],
            ],
        ];
    }

    public static function tableName()
    {
        return '{{%user_forget_password_verification}}';
    }

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['code'], 'string'],
            [['email'], 'email'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->generateCode();
        }

        return true;
    }

    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior('token', \paw\behaviors\TokenQueryBehavior::class);
        return $query;
    }

    public function getIsExpired()
    {
        return $this->getIsTokenExpired();
    }

    public function renew($duration = null)
    {
        $this->generateCode();
        return $this->renewToken($duration);
    }

    public function expire()
    {

    }

    public function generateCode()
    {
        $this->code = $this->generateRandomString();
    }

    protected function generateRandomString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
