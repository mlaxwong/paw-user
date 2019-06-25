<?php
namespace paw\user\models;

use paw\user\models\EmailVerification;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\mail\MailerInterface;

class EmailVerifyRequestForm extends Model
{
    public $user;
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

        $emailVerificationModel = $this->getEmailVerificationModel();
        $this->email = $emailVerificationModel->email;
    }

    public function rules()
    {
        return [
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => \paw\user\models\User::class, 'targetAttribute' => 'email', 'when' => function ($model) {
                return $model->user->email != $this->email;
            }],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->getEmailVerificationModel();
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

    public function getEmailVerificationModel()
    {
        $user = $this->user;
        $model = EmailVerification::findOne(['user_id' => $user->id]);
        $model = $model ? $model : new EmailVerification([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        return $model;
    }

    public function getMailer()
    {
        return $this->_mailer;
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
