<?php

use Phinx\Migration\AbstractMigration;

class AddUpConfigLocation extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('settings');
        $table->addColumn('user_profile_config', 'string', array('after' => 'folder_watch_side_a_match','limit' => 255,'default'=>null,'null' => true))
            ->update();
    }
}
