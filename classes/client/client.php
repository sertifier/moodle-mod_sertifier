<?php
/**
 * Requests with curl
 *
 * @package    mod_sertifier
 * @category   backup
 * @copyright  2021 Faruk Arig
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_sertifier\client;
defined('MOODLE_INTERNAL') || die();

class client {
    /**
     * The curl object used to make the request.
     * @var curl $curl
     */
    private $curl;

    /**
     * The options object for the requests.
     * @var array $curloptions
     */
    private $curloptions;

    /**
     * Constructor method
     *
     * @param stdObject $curl a mock curl for testing
     */
    public function __construct($curl = null) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        // A mock curl is passed when unit testing.
        if ($curl) {
            $this->curl = $curl;
        } else {
            $this->curl = new \curl();
        }

        $token = get_config('sertifier', 'api_key');
        $this->curloptions = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HTTPHEADER'     => array(
                'secretKey: '.$token,
                'Content-Type: application/json',
                'api-version: 2.0'
            )
        );
    }

    /**
     * Post request
     *
     * @param string    $url URL to request
     * @param string    $token This variable sertifier b2b api secret key
     * @param array     $body Request body
     * @return stdClass Request response
     */
    public function post($url, $body) {
        return $this->create_req($url, 'post', $body);
    }

    /**
     * Create request
     *
     * @param string    $url URL to request
     * @param string    $token This variable sertifier b2b api secret key
     * @param string    $method Request method
     * @param array     $body Request body
     * @return stdClass Request response
     */
    private function create_req($url, $method, $body = null) {
        $curl = $this->curl;
        $response = $curl->$method($url, json_encode($body), $this->curloptions);

        return json_decode($response);
    
    }
}