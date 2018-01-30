<?php


use Phinx\Migration\AbstractMigration;

class AddMainTsToJobs extends AbstractMigration
{


    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('posted_main_ts', 'datetime', array('after' => 'checked_at','null'=>true))
            ->addIndex(array('posted_main_ts'), array('unique' => false))
            ->save();

    }
}
