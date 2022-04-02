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

namespace mod_sertifier\apiRest;

use mod_sertifier\client\client;

/**
 * Sertifier B2B api
 *
 * @package    mod_sertifier
 * @copyright  2021 Faruk Arig
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apiRest {
    /** @var string This variable sertifier b2b api base url */
    private $apibaseurl = "https://b2b.sertifier.com";

    /**
     * HTTP request client.
     * @var stdObject $client
     */
    private $client;

    /**
     * Constructor method to define correct endpoints
     */
    public function __construct() {
        $this->client = new client();
    }

    /**
     * Get all Deliveries which belong to your organization.
     *
     * @return stdClass All Deliveries which belong to your organization
     */
    public function get_all_deliveries() {
        return $this->client->post("{$this->apibaseurl}/Delivery/GetAllDeliveries", []);
    }

    /**
     * Add the recipients to the Delivery.
     *
     * @param string    $deliveryid Id of the Delivery
     * @param array     $recipients Recipients list
     * @return stdClass A status indicating success or failure
     */
    public function add_recipients($deliveryid, $recipients) {
        return $this->client->post("{$this->apibaseurl}/Delivery/AddRecipients", [
            "deliveryId" => $deliveryid,
            "recipients" => $recipients
        ]);
    }

    /**
     * Get all Recipients for a specific Delivery.
     *
     * @param string    $deliveryid Id of the Delivery
     * @return stdClass All Recipients for a specific Delivery.
     */
    public function get_recipients($deliveryid) {
        return $this->client->post("{$this->apibaseurl}/Delivery/ListRecipients", [
            "id" => $deliveryid
        ]);
    }

    /**
     * Deletes the recipient and credential corresponding to the given Id.
     *
     * @param array    $certificatenos Certificate no array
     * @return stdClass A status indicating success or failure
     */
    public function delete_recipients($certificatenos) {
        return $this->client->post("{$this->apibaseurl}/Recipient/DeleteCertificates", [
                "certificateNos" => $certificatenos
            ]);
    }

    /**
     * Creates a Delivery.
     *
     * @param string    $title Delivery title
     * @return stdClass A status indicating success or failure
     */
    public function create_delivery($title) {
        return $this->client->post("{$this->apibaseurl}/Moodle/AddDeliveryWithType", [
                "title" => $title,
                "type" => 2
            ]);
    }
}
