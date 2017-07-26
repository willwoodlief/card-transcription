<?php

use Phinx\Migration\AbstractMigration;

class AddCountiesToJobs extends AbstractMigration
{
    /**
     Checks to see if column exists already
     */
    public function up()
    {
        //get page id
        $result = $this->fetchRow(
            "SELECT * 
                    FROM information_schema.COLUMNS 
                    WHERE 
                        TABLE_SCHEMA = 'transcription' 
                    AND TABLE_NAME = 'ht_jobs' 
                    AND COLUMN_NAME = 'county_name' "
        );

        if (!$result) {
            print "Adding in new columns: \n county_name \n county_code \n";
            $table = $this->table('ht_jobs');
            $table->addColumn('county_name', 'string', array('after' => 'city','limit' => 255,'default'=>null,'null' => true))
                ->addColumn('county_code', 'string', array('after' => 'county_name','limit' => 255,'default'=>null,'null' => true))
                ->save();
        } else {
            print "Nothing to do, the columns are already added";
        }






    }

    public function down() {
        $table = $this->table('ht_jobs');
        $table->removeColumn('county_name')
            ->removeColumn('county_code');
    }
}
