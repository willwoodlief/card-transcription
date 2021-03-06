<?php

use Phinx\Migration\AbstractMigration;

class AddCodeToJobs extends AbstractMigration
{
    /**
     adds a unique code for each job
     */
    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('short_code', 'string', array('after' => 'checked_at','limit' => 12,'null'=>true))
            ->addIndex(array('short_code'), array('unique' => true))
            ->save();

        $this->execute('ALTER TABLE ht_jobs MODIFY short_code VARCHAR (12) CHARACTER SET utf8 COLLATE utf8_bin;');
    }
}
