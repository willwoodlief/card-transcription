<?php

use Phinx\Migration\AbstractMigration;

$real =   realpath( dirname( __FILE__ ) );
require_once $real.'/../../vendor/autoload.php';
use Hashids\Hashids;

#I made a config file that is meant to be shared
require_once $real.'/../../config/shared_config.php';

$salt = getenv('HASH_ID_SALT');
$min_length = intval( getenv('HASH_ID_MIN_LENGTH') );

class PopulateJobCodes extends AbstractMigration
{
    /**
     fills in a code for each job, right now its 8 long
     */
    public function up()
    {
        $salt = getenv('HASH_ID_SALT');
        $min_length = intval( getenv('HASH_ID_MIN_LENGTH') );

        if ($min_length == 0 || $salt == "") {
            throw new UnexpectedValueException("Hash ID has empty values, check include path for shared_config.php");
        }

        # see https://github.com/ivanakimov/hashids.php
        $hashids = new Hashids($salt,$min_length);
        $data = [];

        //get job ids
        $stmt = $this->query("SELECT id FROM ht_jobs WHERE 1"); // returns PDOStatement
        $rows = $stmt->fetchAll(); // returns the result as an array
        foreach ($rows as $row) {
            $id = $row['id'];
            $what = $hashids->encode($id);

            $node = ['id'=>$id,'short_code' =>$what];
            array_push($data,$node);
        }

        foreach($data as $row) {
            $id = $row['id'];
            $what = $row['short_code'];
            $this->execute("UPDATE ht_jobs SET short_code = '$what' WHERE id = $id");
            print "Updated $id with $what\n";
        }

    }

    public function down() {
        $this->execute('UPDATE ht_jobs SET short_code = NULL');
    }
}
