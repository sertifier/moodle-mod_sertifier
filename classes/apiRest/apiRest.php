<?php
/**
 * Sertifier B2B api
 *
 * @package    mod_sertifier
 * @category   backup
 * @copyright  2021 Faruk Arig
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_sertifier\apiRest;

use mod_sertifier\client\client;

class apiRest {
    /** @var string This variable sertifier b2b api base url */
    private $apiBaseUrl = "https://b2b.sertifier.com";

    /**
     * HTTP request client.
     * @var stdObject $client
     */
    private $client;

    /**
     * @param int   $token This variable sertifier b2b api secret key
     * @return bool A status indicating success or failure
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
        return $this->client->post("{$this->apiBaseUrl}/Delivery/GetAllDeliveries", []);
    }

    /**
     * Add the recipients to the Delivery.
     *
     * @param string    $deliveryid Id of the Delivery
     * @param array     $recipients Recipients list
     * @return stdClass A status indicating success or failure
     */
    public function add_recipients($deliveryid, $recipients) {
        return $this->client->post("{$this->apiBaseUrl}/Delivery/AddRecipients", [
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
        return $this->client->post("{$this->apiBaseUrl}/Delivery/ListRecipients", [
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
        return $this->client->post("{$this->apiBaseUrl}/Recipient/DeleteCertificates", [
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
        return $this->client->post("{$this->apiBaseUrl}/Moodle/AddDeliveryWithType", [
                "title" => $title,
                "type" => 2
            ]);
    }
}