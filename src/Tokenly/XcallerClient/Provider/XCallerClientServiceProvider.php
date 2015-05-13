<?php

namespace Tokenly\XcallerClient\Provider;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Tokenly\XcallerClient\Client;

/*
* XCallerClientServiceProvider
*/
class XCallerClientServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindConfig();

        $this->app->bind('Tokenly\XcallerClient\Client', function($app) {
            $queue_manager = $this->app->make('Illuminate\Queue\QueueManager');
            $client = new Client(Config::get('xcaller-client.queue_connection'), Config::get('xcaller-client.queue_name'), $queue_manager);
            return $client;
        });
    }

    protected function bindConfig()
    {
        // simple config
        $config = [
            'xcaller-client.queue_connection' => env('XCALLER_QUEUE_CONNECTION', 'blockingbeanstalkd'),
            'xcaller-client.queue_name'       => env('XCALLER_QUEUE_NAME', 'notifications_out'),
        ];

        // set the laravel config
        Config::set($config);
    }

}

