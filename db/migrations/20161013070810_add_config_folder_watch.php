<?php

use Phinx\Migration\AbstractMigration;

class AddConfigFolderWatch extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('settings');
        $table->addColumn('folder_watch', 'string', array('after' => 'sns_arn','limit' => 255,'default'=>null,'null' => true))
            ->update();
    }
}
