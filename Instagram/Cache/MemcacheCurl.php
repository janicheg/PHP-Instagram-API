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
    public function getResponse($curl_resource) {
        $memcache = $this->getMemcache();

        if ($memcache === false) {
            $response = $this->executeCurl($curl_resource);
        } else {
            $curl_info = curl_getinfo($curl_resource);
            $key = md5($curl_info['url']);
            $ttl = 60; // 1 minute

            if ($memlink = $memcache->get($key)) {
                $response = $memlink;
            } else {
                $response = $this->executeCurl($curl_resource);
                $memcache->replace($key, $response, false, $ttl) || $memcache->set($key, $response, false, $ttl);
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

        if (extension_loaded('memcache')) {
            $usemem = true;
        }
        if ($usemem) {
            $memcache_server = 'localhost';
            $memcache_port = '11211';

            $memcache = new \Memcache;
            $memcache->addServer($memcache_server, $memcache_port);

            return $memcache;
        }
        return false;
    }
} 