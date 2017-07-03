<?php

use Phinx\Migration\AbstractMigration;

class BuildingAndStreetToJobs extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('suit', 'string', array('after' => 'title','limit' => 255,'default'=>null,'null' => true))
            ->save();
    }


}
