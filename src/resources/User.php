<?php
namespace paw\user\resources;

class User extends \paw\db\Resource
{
    public static function modelClass()
    {
        return \paw\user\models\User::class;
    }
}