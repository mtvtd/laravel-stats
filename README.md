# This is my package LaravelStats

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mtvtd/laravel-stats.svg?style=flat-square)](https://packagist.org/packages/mtvtd/laravel-stats)
[![Tests](https://github.com/mtvtd/laravel-stats/actions/workflows/run-tests.yml/badge.svg)](https://github.com/mtvtd/laravel-stats/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/mtvtd/laravel-stats/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/mtvtd/laravel-stats/actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mtvtd/laravel-stats.svg?style=flat-square)](https://packagist.org/packages/mtvtd/laravel-stats)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require mtvtd/laravel-stats
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Mtvtd\LaravelStats\LaravelStatsServiceProvider" --tag="laravel-stats-config"
```

Add .env variables: 
```dotenv
# status.mtvtd.nl
LARAVEL_STATS_TOKEN=[your-api-token]
LARAVEL_STATS_SCHEDULER_LOGGING_ENABLED=true
```

Add the update command to de deploy script: 
```bash
php artisan mtvtd:laravel-stats
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Spaan Productions](https://github.com/mtvtd)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
