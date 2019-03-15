<?php
namespace paw\user\models;

use Yii;
use yii\db\ActiveRecord;
use paw\user\web\IdentityInterface;
use paw\behaviors\TimestampBehavior;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return
        [
            [['username'], 'unique'],
            [['username', 'email'], 'required', 'on' => 'default'],
            [['email'], 'email'],
            [['username', 'password_hash', 'email'], 'string', 'min' => 1, 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
        ];
    }

    public static function findByLogin($login)
    {
        return static::find()
            ->andWhere(['or', ['username' => $login], ['email' => $login]])
            ->one();
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
		return static::findOne(['auth_key' => $token]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getLoggedAtColumn()
    {
        return 'logged_at';
    }
}