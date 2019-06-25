<?php
namespace paw\user\models;

use paw\user\models\EmailVerification;
use Yii;
use yii\base\Model;

class EmailVerifyForm extends Model
{
    public $user;
    public $code;

    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
            [['code'], function ($attribute, $params, $validator) {
                $emailVerificationModel = $this->getEmailVerificationModel();
                if ($this->{$attribute} != $emailVerificationModel->code) {
                    $this->addError($attribute, Yii::t('app', 'Invalid cerification code'));
                }
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
            // $user = $this->user;
            // $user->email_verified = true;

            // if (!$user->save()) {
            //     $transaction->rollBack();
            //     if (YII_DEBUG) {
            //         throw new \Exception(print_r($user->errors, 1));
            //     }
            //     return false;
            // }

            $emailVerificationModel = $this->getEmailVerificationModel();
            $emailVerificationModel->delete();

            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
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
}
