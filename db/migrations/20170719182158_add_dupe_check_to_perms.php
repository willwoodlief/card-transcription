<?php

use Phinx\Migration\AbstractMigration;

class AddDupeCheckToPerms extends AbstractMigration
{
    protected $pageId = 56;
    protected $statusName = 'In Progress';

    /**
     * Migrate Up.
     */
    public function up()
    {

        $singleRow =  ['id' => $this->pageId, 'page' => 'pages/get_duplicate_information.php','private'=>1];
        $table = $this->table('pages');
        $table->insert($singleRow);
        $table->saveData();


        $singleRow = ['page_id' => $this->pageId, 'permission_id' => 4]; //transcriber

        $table = $this->table('permission_page_matches');
        $table->insert($singleRow);
        $table->saveData();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('Delete from permission_page_matches where page_id = ' . $this->pageId);
        $this->execute('Delete from pages where id = ' . $this->pageId);
    }
}
