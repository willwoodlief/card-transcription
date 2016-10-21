<?php

use Phinx\Migration\AbstractMigration;

class AddExtensionToJobs extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('ht_jobs');
        $table->addColumn('phone_extension', 'string', array('after' => 'phone','limit' => 255,'default'=>null,'null' => true))
            ->update();
    }
}
