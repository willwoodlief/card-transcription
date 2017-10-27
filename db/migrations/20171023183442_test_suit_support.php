<?php

use Phinx\Migration\AbstractMigration;

//todo set up with utf8mb4._unicode_ci later after all db gets converted

class TestSuitSupport extends AbstractMigration
{
    /**
     note for table encoding. Am using full unicode support, but for mysql server 5.5, unless set with different options in the config, will keep index sizes to less than 767
     * this for strings that have keys, I keep the keys themselves to 150 (4 bytes a character *150) = 600
     * see db/server_config/notes.txt
     * and then do a migration to make all tables ALTER TABLE (ROW_FORMAT=DYNAMIC)
     */
    public function up()
    {
        ########################################Tests##############################################
        $table = $this->table('tests', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "This defines each of the tests done"));
        $table->addColumn('test_name', 'string', array('after' => 'id','limit' => 255,'null' => false,'comment'=>"the name of the test"))
            ->addColumn('class_name', 'string', array('after' => 'test_name','limit' => 255,'null' => false,'comment'=>"smaller groups of a few related tests"))
            ->addColumn('suit_name', 'string', array('after' => 'test_group_minor_name','limit' => 255,'null' => false,'comment'=>"larger groups holding smaller groups"))
            ->addColumn('description', 'text', array('null'=>true,'default'=>null,'comment'=>"optional notes explaining the test"))
            ->addIndex(array('test_name'), array('unique' => true,'limit'=>150))
            ->addIndex(array('class_name'), array('unique' => false,'limit'=>150))
            ->addIndex(array('suit_name'), array('unique' => false,'limit'=>150))
            ->addIndex(array('description'), array('unique' => false,'limit'=>150))
            ->create();

        #######################################Test Runs###################################################

        $table = $this->table('test_runs', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "This marks each time a test is actually run, the test output , and if it passed or not"));

        $table->addColumn('test_id', 'integer', array('null' => false,'comment'=>"this is primary id of the test"))
            ->addColumn('is_passed', 'boolean', ['null' => false,'default'=>false,'length'=>1, 'signed' => false, 'comment'=>'if the test passed then not zero'])
            ->addColumn('test_time_start', 'datetime', array('null' => false,'comment'=> "The start time of the run, add it when creating row"))
            ->addColumn('test_time_end', 'datetime', array('null' => true,'default'=>null,'comment'=> "The end time of the run, update this rown when the test ends"))
            ->addColumn('machine_id', 'string', array('limit' => 255,'null' => false,'comment'=>"the host name of the machine the tests are running on"))
            ->addColumn('machine_type', 'string', array('limit' => 255,'null' => false,'comment'=>"what kind of environment, valid values are [development,production]"))
            ->addColumn('test_type', 'string', array('limit' => 255,'null' => false,'comment'=>"what kind of test, valid values are [cronjob,manual,githook,automatic,other]"))
            ->addColumn('unit_output', 'text', array('null'=>true,'default'=>null,'comment'=>"the console output of the test is saved here"))
            ->addColumn('error_stack_trace', 'text', array('null'=>true,'default'=>null,'comment'=>"If this a failed test, then the stack trace goes here"))
            ->addColumn('notes', 'text', array('null'=>true,'default'=>null,'comment'=>"this can be automaticly entered for certain tests or manually added to explain, or update a status and explanation of a test run"))
            ->addForeignKey('test_id', 'tests', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addIndex(array('test_time_start'), array('unique' => false))
            ->addIndex(array('test_time_end'), array('unique' => false))
            ->addIndex(array('is_passed'), array('unique' => false))
            ->addIndex(array('machine_id'), array('unique' => false,'limit'=>150))
            ->addIndex(array('machine_type'), array('unique' => false,'limit'=>150))
            ->addIndex(array('test_type'), array('unique' => false,'limit'=>150))
            ->addIndex(array('unit_output'), array('unique' => false,'limit'=>150))
            ->addIndex(array('notes'), array('unique' => false,'limit'=>150))
            ->create();

        $trigger = <<<SQL
        CREATE TRIGGER trigger_before_update_test_runs
            BEFORE UPDATE ON test_runs
            FOR EACH ROW
        BEGIN
          IF NEW.machine_type not in ('development','production')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot add or update row in test_runs: machine_type has to be one of [development,production]';
          END IF;
          
          IF NEW.test_type not in ('cronjob','manual','githook','automatic','other')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot add or update row in test_runs: test_type has to be one of [cronjob,manual,githook,automatic,other]';
          END IF;
          
        END
SQL;
        $this->execute($trigger);


        $trigger = <<<SQL
        CREATE TRIGGER trigger_before_create_test_runs
            BEFORE INSERT ON test_runs
            FOR EACH ROW
        BEGIN
          IF NEW.machine_type not in ('development','production')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot add or update row in test_runs: machine_type has to be one of [development,production]';
          END IF;
          
          IF NEW.test_type not in ('cronjob','manual','githook','automatic','other')
          THEN
               SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot add or update row in test_runs: test_type has to be one of [cronjob,manual,githook,automatic,other]';
          END IF;
          
        END
SQL;
        $this->execute($trigger);


        ################################Test Resources#########################################

        $table = $this->table('test_resources', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "This lists the resources used in the tests. For example users and transcriptions. For seeding and locking"));

        $table->addColumn('resource_id', 'integer', array('null' => false,'comment'=>"this is primary id of the resource, look at type_resource to see which table, these can be in different databases so no fk set"))
            ->addColumn('table_name', 'string', array('limit' => 255,'null'=>false,'comment'=>"the table name the resource is in"))
            ->addColumn('db_name', 'string', array('limit' => 255,'null'=>false,'comment'=>"the database the resource is in"))
            ->addColumn('notes', 'text', array('null'=>true,'default'=>null,'comment'=>"any notes for this resource, including explaining why it is used"))
            ->addIndex(array('resource_id'), array('unique' => false))
            ->addIndex(array('table_name'), array('unique' => false,'limit'=>150))
            ->addIndex(array('db_name'), array('unique' => false,'limit'=>150))
            ->addIndex(array('notes'), array('unique' => false,'limit'=>150))
            ->create();



        ####################################### Test Resource Locks#####################################################

        $table = $this->table('test_resource_locks', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "This allows resource locking and sharing between the different tests"));
        $table->addColumn('test_resource_id', 'integer', ['null' => false,'comment'=>"foreign key for test resource"])
            ->addColumn('test_run_id', 'integer', ['null' => false,'comment'=>"foreign key for test run"])
            ->addColumn('lock_time_start', 'datetime', array('null' => false,'comment'=> "The start time of the run, add it when creating row"))
            ->addColumn('lock_time_end', 'datetime', array('null' => true,'default'=>null,'comment'=> "The end time of the run, update this rown when the test ends"))
            ->addForeignKey('test_run_id', 'test_runs', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addForeignKey('test_resource_id', 'test_resources', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addIndex(array('test_resource_id','test_run_id'), array('unique' => true,'name'=>'uidx_each_testrun_locks_resource_only_once'))
            ->addIndex(array('lock_time_end'), array('unique' => false))
            ->addIndex(array('lock_time_start'), array('unique' => false))
            ->create();


        ####################################### Test Seeds ##################################
        $table = $this->table('test_seeds', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "The data that a resource is set to for a certain test. Also tells the test which resources to use"));

        $table->addColumn('test_id', 'integer', ['null' => true,'comment'=>"foreign key for test run. Can be null if used as an ancestor"])
            ->addColumn('test_resource_id', 'integer', ['null' => true,'comment'=>"foreign key for test resource. Can be null if used as an ancestor"])
            ->addColumn('subtype', 'string', array('limit' => 100,'null'=>false,'comment'=>"allows the test to figure out how to use the resources. See each test for information"))
            ->addColumn('ancestors', 'string', array('limit' => 255,'null'=>true,'default'=>null,'comment'=>"any ancestor data is filled in first, this allows tweaking of test sets for other tests without reintering all the data"))
            ->addColumn('notes', 'text', array('null'=>true,'default'=>null,'comment'=>"any notes for this seeding, like explaining that its used as a generic seed, or what the strategy of the data will be"))
            ->addForeignKey('test_id', 'tests', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addForeignKey('test_resource_id', 'test_resources', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addIndex(array('ancestors'), array('unique' => false,'limit'=>160))
            ->addIndex(array('test_id','subtype'), array('unique' => true,'name'=>'uidx_each_test_has_unique_subtype'))
            ->addIndex(array('test_id','test_resource_id'), array('unique' => true,'name'=>'uidx_no_dupicate_resources_per_test'))
            ->addIndex(array('notes'), array('unique' => false,'limit'=>150))
            ->create();


        ######################## Test Seed Values ###################################333
        $table = $this->table('test_seed_values', array('collation' => 'utf8_unicode_ci','encoding'=>'utf8','comment'=> "The actual seed data"));

        $table->addColumn('test_seed_id', 'integer', ['null' => true,'comment'=>"foreign key for the seed"])
            ->addColumn('seed_column_name', 'string', array('limit' => 150,'null'=>false,'comment'=>"the column name to be updated"))
            ->addColumn('seed_data', 'text', array('null'=>true,'default'=>null,'comment'=>"the value of the column. Null here will be made null there"))
            ->addForeignKey('test_seed_id', 'test_seeds', 'id', array('delete'=> 'RESTRICT', 'update'=> 'CASCADE'))
            ->addIndex(array('seed_column_name'), array('unique' => false,'limit'=>150))
            ->addIndex(array('seed_column_name','test_seed_id'), array('unique' => true,'name'=>'uidx_no_duplicate_columns_in_seeds'))
            ->create();


    }


    public function down()
    {

        $this->dropTable('test_seed_values');
        $this->dropTable('test_seeds');
        $this->dropTable('test_resource_locks');
        $this->dropTable('test_resources');
        $this->dropTable('test_runs'); //drops triggers too
        $this->dropTable('tests');
    }
}

