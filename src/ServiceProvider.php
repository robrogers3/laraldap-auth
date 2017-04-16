<?php

namespace robrogers3\laradauth;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * @author   Lucas Vasconcelos <lucas@vscn.co>
 * @package  LSV\LDAP
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**

     * @return void
     */
    public function boot()
    {
        $this->app['auth']->provider('ldap', function($app, array $config) {
            return new UserProvider(
                $app['hash'],
                $config['model'],
                $config['host'],
                $config['domain']
            );
        });
    }
}
