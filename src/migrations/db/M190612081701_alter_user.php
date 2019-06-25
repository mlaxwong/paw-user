<?php
namespace paw\user\migrations\db;

use paw\db\Migration;

class M190612081701_alter_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'email_verified', $this->boolean()->defaultValue(false)->after('email'));
        $this->addColumn('{{%user}}', 'deleted_at', $this->timestamp()->defaultValue(null)->after('updated_at'));
        $this->addColumn('{{%user}}', 'deleted_by', $this->integer()->unsigned()->defaultValue(null)->after('updated_at'));
        $this->addColumn('{{%user}}', 'is_deleted', $this->boolean()->defaultValue(false)->after('updated_at'));
        $this->addForeignKey('fk_user_deleted_by', '{{%user}}', 'deleted_by', '{{%user}}', 'id', 'cascade', 'cascade');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_deleted_by', '{{%user}}');
        $this->dropColumn('{{%user}}', 'email_verified');
        $this->dropColumn('{{%user}}', 'is_deleted');
        $this->dropColumn('{{%user}}', 'deleted_by');
        $this->dropColumn('{{%user}}', 'deleted_at');
    }

    /*
// Use up()/down() to run migration code without a transaction.
public function up()
{

}

public function down()
{
echo "M190612081701_alter_user cannot be reverted.\n";

return false;
}
 */
}
