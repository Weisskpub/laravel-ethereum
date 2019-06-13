<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ethereumd JSON-RPC Scheme
    |--------------------------------------------------------------------------
    | URI scheme of Ethereum Core's JSON-RPC server.
    |
    | Use https to enable SSL connections with Core,
    | but this is strongly discouraged by developers.
    |
    */

    'scheme' => env('ETHEREUMD_SCHEME', 'http'),

    /*
    |--------------------------------------------------------------------------
    | Ethereumd JSON-RPC Host
    |--------------------------------------------------------------------------
    | Tells service provider which hostname or IP address
    | Ethereum Core is running at.
    |
    | If Ethereum Core is running on the same PC as
    | laravel project use localhost or 127.0.0.1.
    |
    | If you're running Ethereum Core on the different PC,
    | you may also need to add rpcallowip=<server-ip-here> to your Ethereum.conf
    | file to allow connections from your laravel client.
    |
    */

    'host' => env('ETHEREUMD_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Ethereumd JSON-RPC Port
    |--------------------------------------------------------------------------
    | The port at which Ethereum Core is listening for JSON-RPC connections.
    | Default is 8545 for mainnet.
    | You can also directly specify port by adding rpcport=<port>
    | to Ethereum.conf file.
    |
    */

    'port' => env('ETHEREUMD_PORT', 8545)
];
