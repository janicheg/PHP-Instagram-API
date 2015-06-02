<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 03.03.15
 * Time: 0:33
 */

namespace Instagram\Cache;


class MemcacheCurl {
    // Call get_link to return a string with a new link
    public function getResponse($curl_resource, $header) {
        $memcache = $this->getMemcache();

        if ($memcache === false) {
            $response = $this->executeCurl($curl_resource);
        } else {
            $curl_info = curl_getinfo($curl_resource);
            $url = $curl_info['url'];
            $key = md5($url);
            $ttl = 60*60; // 1 hour

            if ($memlink = $memcache->get($key)) {
                $this->writeBackgroundTasks(
                    "php ".__DIR__."/backroundRefresh.php",
                    ['url' => escapeshellarg($url), 'header' => escapeshellarg($header)]
                );
                $response = $memlink;
            } else {
                $response = $this->executeCurl($curl_resource);
                $memcache->replace($key, $response, $ttl) || $memcache->set($key, $response, $ttl);
            }
        }
        return $response;
    }

    private function executeCurl($curl_resource)
    {
        $raw_response = curl_exec($curl_resource);
        $error = curl_error( $curl_resource );
        if ( $error ) {
            throw new \Instagram\Core\ApiException( $error, 666, 'CurlError' );
        }
        return $raw_response;
    }

    private function getMemcache() {
        $usemem = false;

        if (extension_loaded('memcached')) {
            $usemem = true;
        }
        if ($usemem) {
            $memcache_server = 'localhost';
            $memcache_port = '11211';

            $memcache = new \Memcached;
            $memcache->addServer($memcache_server, $memcache_port);

            return $memcache;
        }
        return false;
    }

    private function writeBackgroundTasks($cmd, $args)
    {
        $cmd = $cmd . " " . implode(" " ,$args) . " > /dev/null 2>&1 & ";
        $file = file_get_contents($_SERVER['DOCUMENT_ROOT'] .'/memcache_tasks.txt');
        if (!strstr($file, $cmd)) {
            $fp = fopen($_SERVER['DOCUMENT_ROOT'] .'/memcache_tasks.txt', 'a+');
            fwrite($fp, $cmd);
            fclose($fp);
        }
    }
} 