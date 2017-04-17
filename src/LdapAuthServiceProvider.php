<?php

namespace robrogers3\laradauth;

use Illuminate\Support\ServiceProvider;


class LdapAuthServiceProvider extends ServiceProvider
{

    protected $defer = false;

    /**
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'laradauth-auth');
        $this->publishes([
            __DIR__.'/views' => resource_path('views/'),
        ]);
    }

    public function register()
    {
        $this->app['auth']->provider('ldap', function($app, array $config) {
            return new LdapUserProvider(
                $app['hash'],
                $config['model'],
                $config['host'],
                $config['domain'],
                $config['base_dn'],
                $config['user_dn']
            );
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ 'auth' ];
    }
}
