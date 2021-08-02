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
    
    /** @var string This variable sertifier b2b api secret key */
    private $token;

    /**
     * @param int   $token This variable sertifier b2b api secret key
     * @return bool A status indicating success or failure
     */
    public function __construct($token) {
        $this->token = $token;
    }

    /**
     * Get all Deliveries which belong to your organization.
     *
     * @return stdClass All Deliveries which belong to your organization
     */
    public function get_all_deliveries() {
        return client::post("{$this->apiBaseUrl}/Delivery/GetAllDeliveries", $this->token, []);
    }

    /**
     * Add the recipients to the Delivery.
     *
     * @param string    $deliveryid Id of the Delivery
     * @param array     $recipients Recipients list
     * @return stdClass A status indicating success or failure
     */
    public function add_recipients($deliveryid, $recipients) {
        return client::post("{$this->apiBaseUrl}/Delivery/AddRecipients", $this->token, [
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
        return client::post("{$this->apiBaseUrl}/Delivery/ListRecipients", $this->token, [
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
        return client::post("{$this->apiBaseUrl}/Recipient/DeleteCertificates", $this->token, [
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
        return client::post("{$this->apiBaseUrl}/Moodle/AddDeliveryWithType", $this->token, [
                "title" => $title,
                "type" => 2
            ]);
    }
}