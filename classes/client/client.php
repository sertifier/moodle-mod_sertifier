<?php
// This file is part of the Sertifier Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_sertifier\client;

/**
 * Requests with curl
 *
 * @package    mod_sertifier
 * @copyright  2021 Faruk Arig
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
