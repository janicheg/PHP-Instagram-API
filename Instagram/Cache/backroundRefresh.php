<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 03.03.15
 * Time: 1:29
 */


$url = $argv[1];

$curl = curl_init();
curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'GET' );
if($header = $argv[2]) {
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
        'X-Insta-Forwarded-For: ' . $header
    ));
}
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $curl, CURLOPT_URL, $url);

$memcache_server = 'localhost';
$memcache_port = '11211';

$memcache = new \Memcache;
$memcache->addServer($memcache_server, $memcache_port);
$key = md5($url);
$response = curl_exec($curl);
$memcache->replace($key, $response, false, 2*60) || $memcache->set($key, $response, false, 2*60);
