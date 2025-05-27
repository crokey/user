<?php

defined('BASEPATH') or exit('No direct script access allowed');
require '/home/user.bearlapay.com/public_html/MMS/application/vendor/autoload.php';


use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3Client = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'eu-west-1',
    'credentials' => [
        'key'    => 'AKIATLFNSPEQL5BWGCD2',
        'secret' => '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',
    ],
]);

return $s3Client;