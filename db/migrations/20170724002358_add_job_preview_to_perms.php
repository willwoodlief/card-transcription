<?php

use Phinx\Migration\AbstractMigration;

class AddJobPreviewToPerms extends AbstractMigration
{


    # note to make add a new page, just copy this code inside the class and set page to the new name, and set permission 1 though 4
    protected $page = "pages/job_preview.php";
    /**
     * Migrate Up.
     */
    public function up()
    {


        $singleRow =  [ 'page' => $this->page ,'private'=>1];
        $table = $this->table('pages');
        $table->insert($singleRow);
        $table->saveData();

        //get page id
        $result = $this->fetchRow("SELECT id as  last_page from pages WHERE page = '" . $this->page. "'");
        $pageID = $result['last_page'] ;


        $singleRow = ['page_id' => $pageID, 'permission_id' => 4]; //transcriber

        $table = $this->table('permission_page_matches');
        $table->insert($singleRow);
        $table->saveData();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $result = $this->fetchRow("SELECT id as  last_page from pages WHERE page = '" . $this->page. "'");
        $pageID = $result['last_page'] ;
        $this->execute('Delete from permission_page_matches where page_id = ' . $pageID);
        $this->execute('Delete from pages where id = ' . $pageID);
    }
}
