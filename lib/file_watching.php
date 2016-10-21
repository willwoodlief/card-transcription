<?php
// written by will !


class FileWatching
{

    private $folder_to_watch = null;
    protected $db = null;
    protected $rgx_ignore_if_not_match = null;
    protected $rgx_groups = null;
    protected $side_a_equals_this = null;
    protected $n_found = 0;

    public function __construct($folder_to_watch,$rgx_ignore_if_not_match,
                                $rgx_groups,
                                $side_a_equals_this)
    {
        $this->db = DB::getInstance();
        $this->folder_to_watch = $folder_to_watch;
        $this->rgx_ignore_if_not_match = $rgx_ignore_if_not_match;
        $this->rgx_groups = $rgx_groups;
        $this->side_a_equals_this = $side_a_equals_this;
        $this->add_files_to_table();
    }

    public function __destruct() {

    }

    public function get_number_files_found() {
        return $this->n_found;
    }

    public function get_count_pairs_for_processing() {
        $this->db->query('select id , side,common_name,file_path from ht_file_watching
                                    where is_processed = 0 ORDER BY common_name,side;')->count();
    }

    //if callback returns false then pair is not marked as processed
    // else return value will be converted to json and stored as the response
    //callback has the following signature ($side_a,$side_b) where these are full file paths
    public function iterate_pairs(callable $callback) {
        $things = $this->get_pairs_not_processed();
        foreach ($things as $n => $goodies) {
            $side_a = $goodies->side_a;
            $side_b = $goodies->side_b;
            $b_did_process = call_user_func($callback,$side_a,$side_b);
            if ($b_did_process === true || $b_did_process['ok'] == true) {
                $goodies->b_did_process = true;
                $goodies->json_result = json_encode($b_did_process);
            } else if ($b_did_process === false || $b_did_process['ok'] == false) {
                $goodies->b_did_process = false;
                $goodies->json_result = json_encode($b_did_process);
            }
        }

        foreach ($things as $n => $goodies) {
            if (!$goodies->b_did_process)  {
                $this->db->update('ht_file_watching',$goodies->id_side_a,[
                    'is_processed'=>0,
                    'is_processed_ts'=>time(),
                    'response_json'=>$goodies->json_result
                ]) ;

                $this->db->update('ht_file_watching',$goodies->id_side_b,[
                    'is_processed'=>0,
                    'is_processed_ts'=>time(),
                    'response_json'=>$goodies->json_result
                ]) ;
            } else {
                $this->db->update('ht_file_watching',$goodies->id_side_a,[
                    'is_processed'=>1,
                    'is_processed_ts'=>time(),
                    'response_json'=>$goodies->json_result
                ]) ;

                $this->db->update('ht_file_watching',$goodies->id_side_b,[
                    'is_processed'=>1,
                    'is_processed_ts'=>time(),
                    'response_json'=>$goodies->json_result
                ]) ;
            }



        }

    }

    //returns an array of objects where each object is {side_a,side_b,id_side_a,id_side_b}
    private function get_pairs_not_processed() {

        $file_by_name = [];
        $query = $this->db->query('select id , side,common_name,file_path from ht_file_watching
                                    where is_processed = 0 ORDER BY common_name,side;');
        $results = $query->results();
        $ret = [];
        foreach ($results as $rec) {
            if (!isset($file_by_name[$rec->common_name])) {
                $file_by_name[$rec->common_name] = ['count'=>1,
                    'side_a'=>null,'side_b'=>null,
                    'id_side_a'=>null,'id_side_b'=>null];
            } else {
                $file_by_name[$rec->common_name]['count'] ++;

            }
            if ($rec->side == 0) {
                $file_by_name[$rec->common_name]['side_a'] = $rec->file_path;
                $file_by_name[$rec->common_name]['id_side_a'] = $rec->id;
            } else {
                $file_by_name[$rec->common_name]['side_b'] = $rec->file_path;
                $file_by_name[$rec->common_name]['id_side_b'] = $rec->id;
            }

        }

        foreach ($file_by_name as $common => $info) {
            if ($info['count'] != 2) continue;
            array_push($ret,$info);
        }

        return json_decode(json_encode($ret));

    }

    public function is_name_ignored($name) {
        return preg_match($this->rgx_ignore_if_not_match ,$name) != 1;
    }

    public function get_common_name($name) {
        if (preg_match($this->rgx_groups,$name,$matches)) {
            return trim($matches['common']);
        }
        throw new Exception("could not find common name in ". $name . " using ".$this->rgx_groups );

    }

    private function get_side_int($name) {
        $side_name = $this->get_side_name($name);
        if (strcasecmp($side_name,$this->side_a_equals_this) == 0) {return 0;}
        return 1;
    }

    public function get_side_name($name) {
        if (preg_match($this->rgx_groups,$name,$matches)) {
            return trim($matches['side']);
        }
        throw new Exception("could not find side name in ". $name . " using ".$this->rgx_groups );
    }

    private function add_files_to_table() {
        foreach (new DirectoryIterator($this->folder_to_watch) as $fileInfo) {
            if($fileInfo->isDot()) continue;
          //  echo $fileInfo->getMTime() ." -->";
          //  echo $fileInfo->getPath(). '/'. $fileInfo->getFilename() . "\n";
            $ts = $fileInfo->getMTime();
            $name = $fileInfo->getFilename();
            $path = $fileInfo->getRealPath();
            $b_ignore_this = $this->is_name_ignored($name);
            if ($b_ignore_this) continue;

            $common_name = $this->get_common_name($name);
            $side = $this->get_side_int($name);
            //see if in the table
            $many = $this->db->query('select id from ht_file_watching where file_path = ? and file_last_modified_ts = ?',
                [$path,$ts])->count();
            if ($many == 0) {
                $res = $this->db->insert('ht_file_watching',[
                    'file_path'=>$path,
                    'file_last_modified_ts'=>$ts,
                    'file_found_at' => time(),
                    'is_processed' => 0,
                    'common_name' => $common_name,
                    'side' => $side
                ]);
                if (!$res) {
                    throw new Exception('Counld not insert row into table: '. var_dump($this->db->error_info()));
                }
                $this->n_found ++;
            }
        }
    }
}


