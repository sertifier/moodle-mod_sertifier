<?php

namespace mod_sertifier\apiRest;

use mod_sertifier\client\client;

class apiRest {
    private $api_base_url = "https://b2b.sertifier.com";

    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    function get_all_deliveries() {
        return client::post("{$this->api_base_url}/Delivery/GetAllDeliveries", $this->token,[]);
    }

    function add_recipients($deliveryId,$recipients){
        return client::post("{$this->api_base_url}/Delivery/AddRecipients", $this->token,[
            "deliveryId" => $deliveryId,
            "recipients" => $recipients
        ]);
    }

    function get_recipients($deliveryId){
        return client::post("{$this->api_base_url}/Delivery/ListRecipients", $this->token,[
            "id" => $deliveryId
        ]);
    }
    
    function delete_recipients($certificateNos){
        return client::post("{$this->api_base_url}/Recipient/DeleteCertificates", $this->token,[
                "certificateNos" => $certificateNos
            ]);
    }

    function create_delivery($title)
    {
        return client::post("{$this->api_base_url}/Moodle/AddDeliveryWithType", $this->token,[
                "title" => $title,
                "type" => 2
            ]);
    }
}