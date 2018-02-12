<?php

namespace Gazelle;

class BitcoinRPC
{
    public static function __callStatic($Method, $Args)
    {
        if (!defined('BITCOIN_RPC_URL')) {
            return false;
        }
        $MessageID = mt_rand();
        $Params = json_encode(
            [
            'method' => $Method,
            'params' => $Args,
            'id' => $MessageID, ]
        );

        $Request = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => $Params,
                ],
            ];

        if (!$Response = file_get_contents(BITCOIN_RPC_URL, false, stream_context_create($Request))) {
            return false;
        }
        $Response = json_decode($Response);
        if ($Response->id != $MessageID || !empty($Response->error) || empty($Response->result)) {
            return false;
        }
        return $Response->result;
    }
}
