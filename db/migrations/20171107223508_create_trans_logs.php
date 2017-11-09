<?php


use Phinx\Migration\AbstractMigration;

class CreateTransLogs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('ht_log_categories', array( 'collation' => 'utf8_unicode_ci',
                                                                    'encoding'=>'utf8',
                                                                    'comment'=> "An ht_log entry needs to have one of these",
                                                                    'id' => false,
                                                                     'primary_key' => ['category_name']
                                                                    )
        );

        $table->addColumn('category_name', 'string', array('limit' => 30,'null' => false,'comment'=>"the unique category name"))
            ->addColumn('category_type', 'string', array('limit' => 30,'null' => false,'default'=>'text','comment'=>"what kind of data, valid values are [boolean,integer,float,text,json,date_time]"))
            ->addColumn('description', 'text', array('null'=>true,'default'=>null,'comment'=>"optional notes explaining the category"))
            ->addIndex(array('category_name'), array('unique' => true,'limit'=>30))
            ->addIndex(array('category_type'), array('unique' => false,'limit'=>30))
            ->addIndex(array('description'), array('unique' => false,'limit'=>150))
            ->create();


        $trigger = <<<SQL
        CREATE TRIGGER trigger_before_update_ht_log_categories
            BEFORE UPDATE ON ht_log_categories
            FOR EACH ROW
        BEGIN
          IF NEW.category_type not in ('boolean','integer','float','text','json','date_time')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'ht_log_categories::category_type must be one of [boolean,integer,float,text,json,date_time]';
          END IF;
          
        END
SQL;
        $this->execute($trigger);


        $trigger = <<<SQL
        CREATE TRIGGER trigger_before_create_ht_log_categories
            BEFORE INSERT ON ht_log_categories
            FOR EACH ROW
        BEGIN
          IF NEW.category_type not in ('boolean','integer','float','text','json','date_time')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'ht_log_categories::category_type must be one of [boolean,integer,float,text,json,date_time]';
          END IF;
          
        END
SQL;
        $this->execute($trigger);


        $table = $this->table('ht_logs', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "Log Entries"));

        $table->addColumn('ht_job_id', 'integer', ['null' => false,'comment'=>"foreign key for the htjob"])
            ->addColumn('category_name', 'string', array('limit' => 30,'null'=>false,'comment'=>"the category of the entry"))
            ->addColumn('created_at', 'timestamp', array('null'=>false,'default'=>'CURRENT_TIMESTAMP','comment'=>"when the entry was created"))
            ->addColumn('action_version', 'integer', array('null'=>true,'default'=>'1','comment'=>"the version # of the action handling this "))
            ->addColumn('action_name', 'string', array('limit' => 30,'null'=>true,'default'=>null,'comment'=>"the action name handling this"))
            ->addColumn('log_entry', 'text', array('null'=>true,'default'=>null,'comment'=>"the contents of the log"))
            ->addColumn('options_json', 'text', array('null'=>true,'default'=>null,'comment'=>"the options json string that turn it on or off, or dictate behavior"))
            ->addColumn('inputs_json', 'text', array('null'=>true,'default'=>null,'comment'=>"the inputs json string that that was used to do this action"))
            ->addForeignKey('ht_job_id', 'ht_jobs', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addForeignKey('category_name', 'ht_log_categories', 'category_name', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addIndex(array('created_at'), array('unique' => false))
            ->addIndex(array('ht_job_id','category_name','created_at'), array('unique' => true,'name'=>'uidx_job_category_times'))
            ->addIndex(array('log_entry'), array('unique' => false,'limit'=>60))
            ->addIndex(array('options_json'), array('unique' => false,'limit'=>60))
            ->create();


    }

    public function down() {

        $this->dropTable('ht_logs');
        $this->dropTable('ht_log_categories');

    }
}
