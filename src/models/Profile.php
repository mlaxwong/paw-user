<?php
namespace paw\user\models;

use yii\db\ActiveRecord;

class Profile extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_profile}}';
    }
}
