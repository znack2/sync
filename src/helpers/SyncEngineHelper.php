<?php
namespace Usedesk\SyncEngineIntegration\helpers;

class SyncEngineHelper {

    private static $addr;

    public static function getAddr()
    {
        if (!self::$addr) {
            self::$addr = env('SYNC_ENGINE_ADDR', 'localhost:5555');
        }

        return self::$addr;
    }

    public static function createAccount(array $params)
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json' . PHP_EOL,
                'content' => json_encode($params),
                'ignore_errors' => true
            ),
        ));

        $response = file_get_contents(
            'http://' . self::getAddr() . '/connect/authorize',
            false,
            $context);

        return json_decode($response, true);
    }

}