<?php


use Phinx\Migration\AbstractMigration;

class AddMainCodeToJobs extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('main_code', 'integer', array('after' => 'posted_main_ts','null'=>true))
            ->save();
    }
}
