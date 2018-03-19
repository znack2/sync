<?php
namespace usedesk\SyncEngineIntegration\helpers;

class SyncEngineHelper {

    private static $addr;

    public static function getAddr()
    {
        if (!self::$addr) {
            self::$addr = env('SYNC_ENGINE_ADDR', 'http://127.0.0.1:5555');
        }

        return self::$addr;
    }

    public static function createAccount(array $params)
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                'content' => http_build_query($params),
            ),
        ));

        $response = file_get_contents(
            self::getAddr() . '/connect/authorize',
            $use_include_path = false,
            $context);

        return json_encode($response);
    }

}