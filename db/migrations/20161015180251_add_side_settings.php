<?php

use Phinx\Migration\AbstractMigration;

class AddSideSettings extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('settings');
        $table->addColumn('folder_watch_side_a_match', 'string', array('after' => 'folder_watch_group_rgx','limit' => 255,'default'=>null,'null' => true))
            ->update();
    }
}
