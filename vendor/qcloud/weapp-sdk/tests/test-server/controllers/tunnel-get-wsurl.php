<?php
defined('FW') OR exit('No direct script access allowed');

send(array(
    'code' => 0,
    'message' => 'OK',
    'data' => json_encode(array(
        'tunnelId' => '0ddc8b1f-615c-4293-8f4c-ccf9ca492d8d',
        'connectUrl' => 'wss://clhadr5w.ws.qcloud.la/qcloud/ws',
    ), JSON_UNESCAPED_UNICODE),
    'signature' => 'fake_signature',
));
