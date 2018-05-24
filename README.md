# laraldap-auth: Authenticate against your Ldap Server

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


Drop in replacement for Laravel authentication against your ldap service.

Supporting OpenLDAP, 

## Install

Via Composer

``` bash
$ composer require robrogers3/laraldap-auth
```


## Usage
Add this to app.php in the services providers list

```php
robrogers3\laradauth\LdapAuthServiceProvider::class,
```


Update config/auth.php 

``` php
    'providers' => [
        'users' => [
           'driver' => 'ldap',
           'model' => App\User::class,
           'host' => 'host.example..com',
           'domain' => 'example.com',
           'base_dn' => 'cn=users,dc=cs-ds1-1,dc=home,dc=example,dc=com',
           'user_dn' => 'uid'
        ],
    ],

```

Create your database, and specify database connection options in .env and/or config/database.php

Use Artisan to make auth and migrate

Run:
```bash
php artisan make:auth

```

If you are not using Bootstrap or are not using Bootstrap 4 then you can publish this the views to prevent registration.
```bash
php artisan migrate
```

```bash
php artisan vendor:publish --force #force cause we override those in make auth.
```


You may be done. Go ahead and login.

## Using AES to encrypt passwords

The LDAP passwords are saved in the User table. Normally they are encrypted wih BCrypt.

There is now AES support so you can safely exchange information from other applications that require an ldap login for authentication.

With AES and a shared key, you can encrypt and decrypt passwords on either side if you share the same AES key.

Here's the changes you need to make:

1. Add the packages HashServiceProvider to config/app.php

```php
        /*
         * Package Service Providers...
         */
        robrogers3\laradauth\LdapAuthServiceProvider::class,
        robrogers3\laradauth\HashServiceProvider::class,
```

Update the config/hashing.php file like so. 

```php
    'driver' => 'aes',

    //more config here

    'aes' => [
        'key' => 'shared-secret-key'
    ]
```

Now you should be good to go.


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test me not
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email robrogers@me.com instead of using the issue tracker.

## Credits

- [Rob Rogers][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/robrogers3/laraldap-auth.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/robrogers3/laradauth/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/robrogers3/laraldap-auth.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/robrogers3/laraldap-auth.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/robrogers3/laraldap-auth.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/robrogers3/laraldap-auth
[link-travis]: https://travis-ci.org/robrogers3/laraldap-auth
[link-scrutinizer]: https://scrutinizer-ci.com/g/robrogers3/laradauth/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/robrogers3/laraldap-auth
[link-downloads]: https://packagist.org/packages/robrogers3/laraldap-auth
[link-author]: https://github.com/robrogers3
[link-contributors]: ../../contributors
