<?php

use Phinx\Migration\AbstractMigration;

class AddFileWatching extends AbstractMigration
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
    public function change()
    {
        $files = $this->table('ht_file_watching');
        $files->addColumn('file_path', 'string', array('limit' => 255,'null'=>false))
            ->addColumn('common_name', 'string', array('limit' => 255,'null'=>false))
            ->addColumn('side', 'integer', array('null'=>false))

            ->addColumn('file_last_modified_ts', 'integer', array('null'=>false))
            ->addColumn('is_processed', 'integer', array('default' => 0))
            ->addColumn('is_processed_ts', 'integer', array('default' => null))

            ->addColumn('file_found_at', 'integer', array('default' => 0))
            ->addColumn('response_json', 'text', array('null'=>true,'default'=>null))
            ->addColumn('user_name', 'string', array('null'=>true,'default'=>null,'limit' => 255))
            ->addColumn('profile', 'string', array('null'=>true,'default'=>null,'limit' => 255))
            ->addIndex(array('file_path', 'file_last_modified_ts'), array('unique' => true))
            ->addIndex(array('common_name', 'side'), array('unique' => true))
            ->addIndex(array('is_processed'), array('unique' => false))
            ->create();

    }
}
