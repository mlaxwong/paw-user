<?php
namespace paw\user\models;

use paw\user\models\ForgetPasswordVerification;
use Yii;
use yii\base\Model;

class PasswordResetForm extends Model
{
    public $email;
    public $code;

    public $password;
    public $password_confirm;

    public function rules()
    {
        return [
            [['password', 'password_confirm'], 'required'],
            [['password'], 'string', 'min' => 6],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $forgetPasswordVerificationModel = $this->getForgetPasswordVerificationModel();

            $user = $forgetPasswordVerificationModel->user;
            $user->password = $this->password;

            if (!$user->save()) {
                $transaction->rollBack();
                return false;
            }

            $forgetPasswordVerificationModel->delete();

            $transaction->commit();
            return true;
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
    }

    public function getForgetPasswordVerificationModel()
    {
        $model = ForgetPasswordVerification::findOne([
            'email' => $this->email,
            'code' => $this->code
        ]);

        if (!$model) {
            return null;
        } else if ($model->isExpired) {
            $model->delete();
            return null;
        } else {
            $model->renew();
            return $model;
        }
    }
}
