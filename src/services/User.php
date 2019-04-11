<?php
namespace paw\user\services;

class User extends \yii\web\User
{
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if (is_array($permissionName)) {
            $can = true;
            foreach ($permissionName as $name) 
            {
                if (!parent::can($permissionName, $params, $allowCaching)) {
                    $can = false;
                    break;
                }
            }
            return $can;
        } else {
            return parent::can($permissionName, $params, $allowCaching);
        }
    }
}