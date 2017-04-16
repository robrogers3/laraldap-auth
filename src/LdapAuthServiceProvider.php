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
        $this->app['auth']->provider('ldap', function($app, array $config) {
            return new LdapUserProvider(
                $app['hash'],
                $config['model'],
                $config['host'],
                $config['domain'],
                $config['basedn'],
                $config['userdn']
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
