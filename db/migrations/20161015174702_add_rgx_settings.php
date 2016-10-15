<?php

use Phinx\Migration\AbstractMigration;

class AddRgxSettings extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('settings');
        $table->addColumn('folder_watch_filter_rgx', 'string', array('after' => 'folder_watch','limit' => 255,'default'=>null,'null' => true))
            ->addColumn('folder_watch_group_rgx', 'string', array('after' => 'folder_watch_filter_rgx','limit' => 255,'default'=>null,'null' => true))
            ->update();
    }
}
