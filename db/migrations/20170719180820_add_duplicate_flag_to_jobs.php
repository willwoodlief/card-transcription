<?php

use Phinx\Migration\AbstractMigration;

class AddDuplicateFlagToJobs extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('duplicate', 'integer', array('after' => 'is_initialized','default'=>0,'null' => false))
            ->save();
    }
}
