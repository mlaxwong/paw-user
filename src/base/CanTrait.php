<?php
namespace paw\user\base;

use Yii;

trait CanTrait
{
    public $accessChecker;

    protected $_access = [];

    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if (is_array($permissionName)) {
            $can = true;
            foreach ($permissionName as $name) 
            {
                if (!$this->_can($name, $params, $allowCaching)) {
                    $can = false;
                    break;
                }
            }
            return $can;
        } else {
            return $this->_can($permissionName, $params, $allowCaching);
        }
    } 

    protected function _can($permissionName, $params = [], $allowCaching = true)
    {
        if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
            return $this->_access[$permissionName];
        }
        if (($accessChecker = $this->getAccessChecker()) === null) {
            return false;
        }
        $access = $accessChecker->checkAccess($this->getId(), $permissionName, $params);
        if ($allowCaching && empty($params)) {
            $this->_access[$permissionName] = $access;
        }

        return $access;
    }

    protected function getAccessChecker()
    {
        return $this->accessChecker !== null ? $this->accessChecker : $this->getAuthManager();
    }

    protected function getAuthManager()
    {
        return Yii::$app->getAuthManager();
    }
}