# laraldap-auth: Authenticate against

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


Drop in replacement for authenticating against your ldap service.

Supporting OpenLDAP, 

## Install

Via Composer

``` bash
$ composer require robrogers3/laraldap-auth
```

## Usage
Add this to app.php in the services providers list

```php
robrogers3\laradauth\LdapServiceProvider::class,
```


Update config/auth.php 
``` php

    'providers' => [
        'users' => [
           'driver' => 'ldap',
           'model' => App\User::class,
           'host' => 'cs-ds1-1.home.crowdstar.com',
           'domain' => 'crowdstar.com',
           'base_dn' => 'cn=users,dc=cs-ds1-1,dc=home,dc=crowdstar,dc=com',
           'user_dn' => 'uid'
        ],
    ],

```

Create your database, and specify database connection options in .env and/or config/database.php

Use Artisan to make auth

Run:
```bash
php artisan make:auth
```

You may be done. Go ahead and login.

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

[ico-version]: https://img.shields.io/packagist/v/robrogers3/laradauth.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/robrogers3/laradauth/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/robrogers3/laradauth.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/robrogers3/laradauth.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/robrogers3/laradauth.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/robrogers3/laradauth
[link-travis]: https://travis-ci.org/robrogers3/laradauth
[link-scrutinizer]: https://scrutinizer-ci.com/g/robrogers3/laradauth/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/robrogers3/laradauth
[link-downloads]: https://packagist.org/packages/robrogers3/laradauth
[link-author]: https://github.com/robrogers3
[link-contributors]: ../../contributors
