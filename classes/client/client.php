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
     * Post request
     *
     * @param string    $url URL to request
     * @param string    $token This variable sertifier b2b api secret key
     * @param array     $body Request body
     * @return stdClass Request response
     */
    public static function post($url, $token, $body) {
        return self::create_req($url, $token, 'POST', $body);
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
    public static function create_req($url, $token, $method, $body = null) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (isset($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'secretKey: '.$token,
            'Content-Type: application/json',
            'api-version: 2.0'
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}