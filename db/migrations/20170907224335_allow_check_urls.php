<?php

use Phinx\Migration\AbstractMigration;

class AllowCheckUrls extends AbstractMigration
{
    protected $page = "pages/check_url_exists.php";
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
