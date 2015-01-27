<?php

/**
 * Instagram PHP
 * @author Galen Grover <galenjr@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

namespace Instagram\Net;

/**
 * Curl Client
 *
 * Uses curl to access the API
 */
class CurlClient implements ClientInterface {

    /**
     * Curl Resource
     *
     * @var curl resource
     */
    protected $curl = null;
    protected $config = null;
    protected $client_secret = null;
    /**
     * Constructor
     *
     * Initializes the curl object
     */
    function __construct(){
        $this->initializeCurl();
    }

    public function setClientSecret($client_secret){
        $this->client_secret = $client_secret;
        $this->initializeCurl();
    }

    /**
     * GET
     *
     * @param string $url URL to send get request to
     * @param array $data GET data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function get( $url, array $data = null ){
	if(!strpos($url,'follow')) {
            $data['count']="18";
        }
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
        return $this->fetch();
    }

    /**
     * POST
     *
     * @param string $url URL to send post request to
     * @param array $data POST data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function post( $url, array $data = null ) {
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt( $this->curl, CURLOPT_URL, $url );
        curl_setopt( $this->curl, CURLOPT_POSTFIELDS, http_build_query( $data ) );
        return $this->fetch();
    }

    /**
     * PUT
     *
     * @param string $url URL to send put request to
     * @param array $data PUT data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function put( $url, array $data = null ){
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
    }

    /**
     * DELETE
     *
     * @param string $url URL to send delete request to
     * @param array $data DELETE data
     * @return \Instagram\Net\Response
     * @access public
     */
    public function delete( $url, array $data = null ){
        curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
        curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
        return $this->fetch();
    }

    /**
     * Initialize curl
     *
     * Sets initial parameters on the curl object
     *
     * @access protected
     */
    protected function initializeCurl() {
        $this->curl = curl_init();
        if(isset($this->client_secret)) {
            $ips = $_SERVER['REMOTE_ADDR'];
            $secret = $this->client_secret;
            $signature = (hash_hmac('sha256', $ips, $secret, false));
            $header = join('|', array($ips, $signature));

            curl_setopt( $this->curl, CURLOPT_HTTPHEADER, array(
                    'X-Insta-Forwarded-For: ' . $header
                ));
        }
        curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, false );
    }

    /**
     * Fetch
     *
     * Execute the curl object
     *
     * @return StdClass
     * @access protected
     * @throws \Instagram\Core\ApiException
     */
    protected function fetch() {
        $raw_response = curl_exec( $this->curl );
        $error = curl_error( $this->curl );
        if ( $error ) {
            throw new \Instagram\Core\ApiException( $error, 666, 'CurlError' );
        }
        return $raw_response;
    }

}
