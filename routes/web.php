<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'v1', 'namespace' => 'V1'], function () use ($router) {
        $router->group(['prefix' => 'clients'], function () use ($router) {
            $router->get('/soap/wsdl', [
                'as' => 'clients-soap-wsdl',
                'uses' => 'ClientController@wsdlAction'
            ]);

            $router->post('/soap/server', [
                'as' => 'clients-soap-server',
                'uses' => 'ClientController@serverAction'
            ]);
        });

        $router->group(['prefix' => 'wallets'], function () use ($router) {
            $router->get('/soap/wsdl', [
                'as' => 'wallets-soap-wsdl',
                'uses' => 'WalletController@wsdlAction'
            ]);

            $router->post('/soap/server', [
                'as' => 'wallets-soap-server',
                'uses' => 'WalletController@serverAction'
            ]);
        });

        $router->group(['prefix' => 'payments'], function () use ($router) {
            $router->get('/soap/wsdl', [
                'as' => 'payments-soap-wsdl',
                'uses' => 'PaymentController@wsdlAction'
            ]);

            $router->post('/soap/server', [
                'as' => 'payments-soap-server',
                'uses' => 'PaymentController@serverAction'
            ]);
        });
    });
});
