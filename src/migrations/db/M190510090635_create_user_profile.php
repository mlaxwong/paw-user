<?php
namespace paw\user\migrations\db;

use paw\db\Migration;
use paw\user\models\User;

class M190510090635_create_user_profile extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_profile}}', [
            'id' => $this->integer()->unsigned(),
            'user_id' => $this->integer()->unsigned()->defaultValue(null),
            'firstname' => $this->string()->defaultValue(null),
            'lastname' => $this->string()->defaultValue(null),
            'gender' => $this->string()->defaultValue(null),
            'is_main' => $this->boolean()->defaultValue(false),
        ]);
        $this->addForeignKey('fk_user_profile_user_id', '{{%user_profile}}', 'user_id', User::tableName(), 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_profile_user_id', '{{%user_profile}}');
        $this->dropTable('{{%user_profile}}');
    }

    /*
// Use up()/down() to run migration code without a transaction.
public function up()
{

}

public function down()
{
echo "M190510090635_create_user_profile cannot be reverted.\n";

return false;
}
 */
}
