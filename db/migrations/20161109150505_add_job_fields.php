<?php

use Phinx\Migration\AbstractMigration;

class AddJobFields extends AbstractMigration
{
   /*
    * these fields add to the jobs per project 2
    * added: country,twitter,home_phone,other_phone
    * renamed: designations to title, phone to work_phone, phone_extension to work_phone_extension
    * removed: other_category, other_value
    */

    public function up()
    {
        $table = $this->table('ht_jobs');
        $table->removeColumn('other_value')
            ->removeColumn('other_category')
            ->renameColumn('designations', 'title')
            ->renameColumn('phone', 'work_phone')
            ->renameColumn('phone_extension', 'work_phone_extension')

            ->addColumn('country', 'string', array('after' => 'zip','limit' => 255,'default'=>null,'null' => true))
            ->addColumn('twitter', 'string', array('after' => 'skype','limit' => 255,'default'=>null,'null' => true))
            ->addColumn('home_phone', 'string', array('after' => 'cell_phone','limit' => 255,'default'=>null,'null' => true))
            ->addColumn('other_phone', 'string', array('after' => 'home_phone','limit' => 255,'default'=>null,'null' => true))
            ->save();
    }

    public function down() {
        $table = $this->table('ht_jobs');
        $table->addColumn('other_value', 'string', array('after' => 'skype','limit' => 255,'default'=>null,'null' => true))
            ->addColumn('other_category', 'string', array('after' => 'other_value','limit' => 255,'default'=>null,'null' => true))

            ->renameColumn('title','designations' )
            ->renameColumn('work_phone','phone' )
            ->renameColumn('work_phone_extension','phone_extension' )
            ->removeColumn('country')
            ->removeColumn('twitter')
            ->removeColumn('home_phone')
            ->removeColumn('other_phone')
            ->save();
    }
}
