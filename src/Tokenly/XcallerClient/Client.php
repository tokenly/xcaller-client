<?php

namespace Tokenly\XcallerClient;

use Exception;

/*
* Client
* A Counterparty client
*/
class Client
{

    public function __construct($queue_connection, $queue_name, $queue_manager) {
        $this->queue_connection = $queue_connection;
        $this->queue_name       = $queue_name;
        $this->queue_manager    = $queue_manager;
    }

    public function sendWebhookWithReturn($payload, $endpoint, $id=null, $api_token=null, $api_secret=null) {
        return $this->sendWebhook($payload, $endpoint, $id, $api_token, $api_secret);
    }

    public function sendWebhookWithoutReturnQueue($payload, $endpoint, $id=null, $api_token=null, $api_secret=null) {
        $meta_overrides = ['returnTubeName' => false,];
        return $this->sendWebhook($payload, $endpoint, $id, $api_token, $api_secret, $meta_overrides);
    }

    public function sendWebhook($payload, $endpoint, $id=null, $api_token=null, $api_secret=null, $meta_overrides=null) {

        if (is_array($payload)) { $payload = $this->encodePayload($payload); }

        if ($api_secret !== null) {
            $signature = hash_hmac('sha256', $payload, $api_secret, false);
        } else {
            $signature = null;
        }

        $meta = [
            'id'        => ($id !== null ? $id : round(microtime() * 1000)),
            'endpoint'  => $endpoint,
            'timestamp' => time(),
            'apiToken'  => $api_token,
            'signature' => $signature,
            'attempt'   => 0,
        ];
        if ($meta_overrides !== null) {
            $meta = array_merge($meta, $meta_overrides);
        }

        $notification_entry = [
            'meta'    => $meta,
            'payload' => $payload,
        ];

        if ($api_token === null) { unset($notification_entry['meta']['apiToken']); }
        if ($api_secret === null) { unset($notification_entry['meta']['signature']); }

        // put notification in the queue
        $this->queue_manager
            ->connection($this->queue_connection)
            ->pushRaw(json_encode($notification_entry), $this->queue_name);
    }

    
    protected function encodePayload($payload) {
        return json_encode($payload);
    }
}

