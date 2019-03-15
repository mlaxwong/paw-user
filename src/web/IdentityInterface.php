<?php
namespace paw\user\web;

interface IdentityInterface extends \yii\web\IdentityInterface
{
    public function getLoggedAtColumn();
}