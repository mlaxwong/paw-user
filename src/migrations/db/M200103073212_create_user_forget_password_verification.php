<?php
namespace paw\user\migrations\db;

use paw\db\Migration;

class M200103073212_create_user_forget_password_verification extends Migration
{
    use \paw\db\TextTypesTrait;
    use \paw\db\DefaultColumn;

    public function safeUp()
    {
        $this->createTable('{{%user_forget_password_verification}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->defaultValue(null),
            'email' => $this->string()->defaultValue(null),
            'code' => $this->string()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_user_forget_password_verification_user_id', '{{%user_forget_password_verification}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_forget_password_verification_user_id', '{{%user_forget_password_verification}}');
        $this->dropTable('{{%user_forget_password_verification}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M200103073212_create_user_forget_password_verification cannot be reverted.\n";

        return false;
    }
    */
}