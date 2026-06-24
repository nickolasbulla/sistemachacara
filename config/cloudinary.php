<?php
require_once __DIR__ . '/../vendor/autoload.php';

$config = new \Cloudinary\Configuration\Configuration();
$config->cloud->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
$config->cloud->apiKey    = $_ENV['CLOUDINARY_API_KEY'];
$config->cloud->apiSecret = $_ENV['CLOUDINARY_API_SECRET'];

return new \Cloudinary\Cloudinary($config);
