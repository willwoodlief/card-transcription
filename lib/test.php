<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/lib/aws/aws-autoloader.php';

$sharedConfig = [
    'region'  => getenv('AWS_REGION'),
    'version' => 'latest'
];

// Create an SDK class used to share configuration across clients.
$sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
$s3Client = $sdk->createS3();

$promise = $s3Client->listBucketsAsync();
$promise
    ->then(function ($result) {
        echo 'Got a result: ' . var_export($result, true);
        echo '<hr>';
        $names = $result->search('Buckets[].Name');
        echo implode(',',$names);
    })
    ->otherwise(function ($reason) {
        echo 'Encountered an error: ' . $reason->getMessage();
    });