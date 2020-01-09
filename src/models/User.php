<?php
namespace paw\user\models;

use paw\behaviors\TimestampBehavior;
use paw\user\web\IdentityInterface;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

class User extends ActiveRecord implements IdentityInterface
{
    use \paw\user\base\CanTrait;

    const STATUS_ACTIVE = 'active';
    const STATUS_BANNED = 'banned';
    const STATUS_INAVTIVE = 'inactive';

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'auth_key' => [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key',
                ],
                'value' => Yii::$app->getSecurity()->generateRandomString(),
            ],
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
            [['first_name', 'last_name'], 'string'],
            [['auth_key'], 'string', 'max' => 32],
            [['email_verified'], 'boolean'],
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
        $this->generateAuthKey();
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

    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'id'])->andOnCondition(['is_main' => true]);
    }

    public function setEmailVerified($email = null)
    {
        $this->email_verified = true;
        return $this->save();
    }
}
