<?php
namespace paw\user\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\mail\MailerInterface;
use paw\user\models\ForgetPasswordVerification;
use paw\user\models\User;

class ForgetPasswordForm extends Model
{
    public $email;
    public $mailTemplate;
    public $mailSubject;
    public $mailFrom;

    protected $_mailer = null;

    public function init()
    {
        parent::init();
        if ($this->_mailer === null) {
            throw new InvalidConfigException(Yii::t('app', 'Config "mailer" is required'));
        }
    }

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'email'],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->getForgetPasswordVerificationModel();
            $model->email = $this->email;
            if ($model->isExpired) {
                $model->renew();
            }
            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $user = $this->user;
            $user->email = $model->email;
            if (!$user->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->sendVerifyEmail();

            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollBack();
            if (YII_DEBUG) {
                if ($model->errors) {
                    throw new \Exception(print_r($model->errors, 1));
                } else {
                    throw $ex;
                }
            }
            return false;
        }
        return false;
    }

    public function setMailer(MailerInterface $mailer)
    {
        $this->_mailer = $mailer;
    }

    public function getForgetPasswordVerificationModel()
    {
        $user = $this->user;
        $model = ForgetPasswordVerification::findOne(['user_id' => $user->id]);
        $model = $model ? $model : new ForgetPasswordVerification([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        return $model;
    }

    public function getMailer()
    {
        return $this->_mailer;
    }

    protected function getUser()
    {
        return User::findOne(['email' => $this->email]);
    }

    protected function sendVerifyEmail()
    {
        $mailer = $this->getMailer();
        return $mailer->compose($this->mailTemplate, ['model' => $this])
            ->setFrom($this->mailFrom)
            ->setTo($this->email)
            ->setSubject($this->mailSubject)
            ->send();
    }
}
