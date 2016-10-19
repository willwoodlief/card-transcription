<?php
$real =   realpath( dirname( __FILE__ ) );
require_once $real.'/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

class WatchingConfigs
{

    private $yml_file = null;
    private $yml_hash = null;
    private $yml_obj = null;


    public function __construct($yml_file)
    {

        $this->yml_file = $yml_file;
        $this->yml_hash = Yaml::parse(file_get_contents($yml_file));
        $this->yml_obj = json_decode(json_encode($this->yml_hash));
    }

    public function __destruct(){ }

    public function get_configs() { return $this->yml_obj;}

}