<?php

namespace Weisskpub\Ethereum\Providers;

use Weisskpub\Ethereum\Client as EthereumClient;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../../config/config.php');

        $this->publishes([$path => config_path('ethereumd.php')], 'config');
        $this->mergeConfigFrom($path, 'ethereumd');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAliases();

        $this->registerClient();
    }

    /**
     * Register aliases.
     *
     * @return void
     */
    protected function registerAliases()
    {
        $aliases = [
            'ethereumd' => 'Weisskrub\Ethereum\Client',
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }
    }

    /**
     * Register client.
     *
     * @return void
     */
    protected function registerClient()
    {
        $this->app->singleton('ethereumd', function ($app) {
            return new EthereumClient([
                'scheme' => $app['config']->get('ethereumd.scheme', 'http'),
                'host'   => $app['config']->get('ethereumd.host', 'localhost'),
                'port'   => $app['config']->get('ethereumd.port', 8545),
            ]);
        });
    }
}
