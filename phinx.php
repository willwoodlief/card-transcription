<?php
$real =   realpath( dirname( __FILE__ ) );
require_once $real.'/users/private_init.php';

return array(
    "paths" => array(
        "migrations" => "db/migrations",
        "seeds" => "db/seeds"
    ),
    "environments" => array(
        "default_migration_table" => "phinxlog",
        "default_database" => "dev",
        "dev" => array(
            "adapter" => "mysql",
            "host" => getenv('DB_HOST'),
            "name" => getenv('DB_NAME'),
            "user" => getenv('DB_USERNAME'),
            "pass" => getenv('DB_PASSWORD'),
            "port" => getenv('DB_PORT'),
            "charset" => getenv('DB_CHARSET')
        ),
        "production" => array(
            "adapter" => "mysql",
            "host" => getenv('PRODUCTION_DB_HOST'),
            "name" => getenv('PRODUCTION_DB_NAME'),
            "user" => getenv('PRODUCTION_DB_USERNAME'),
            "pass" => getenv('PRODUCTION_DB_PASSWORD'),
            "port" => getenv('PRODUCTION_DB_PORT'),
            "charset" => getenv('PRODUCTION_DB_CHARSET')
        )
    )
);