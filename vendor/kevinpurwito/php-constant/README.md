# About PhpConstant

[![Tests](https://github.com/kevinpurwito/php-constant/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/kevinpurwito/php-constant/actions/workflows/run-tests.yml)
[![Code Style](https://github.com/kevinpurwito/php-constant/actions/workflows/php-cs-fixer.yml/badge.svg?branch=main)](https://github.com/kevinpurwito/php-constant/actions/workflows/php-cs-fixer.yml)
[![Psalm](https://github.com/kevinpurwito/php-constant/actions/workflows/psalm.yml/badge.svg?branch=main)](https://github.com/kevinpurwito/php-constant/actions/workflows/psalm.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kevinpurwito/php-constant.svg?style=flat-square)](https://packagist.org/packages/kevinpurwito/php-constant)
[![Total Downloads](https://img.shields.io/packagist/dt/kevinpurwito/php-constant.svg?style=flat-square)](https://packagist.org/packages/kevinpurwito/php-constant)

PhpConstant is a simple class containing useful functions which you can extend to your constant classes.

For example if you want to have a StatusConst class, which contains all possible statuses as constants, 
you can extend PhpConstant to use its functions for your StatusConst class.  

## Installation

You can install the package via composer:

```bash
composer require kevinpurwito/php-constant
```

## Usage

```php
use KevinPurwito\PhpConstant\PhpConstant;

// Class Extends PhpConstant to use its functions
class StatusConst extends PhpConstant
{
    const PENDING = 'pending';
    const IN_PROCESS = 'in_process';
    const COMPLETED = 'completed';
}

/*
|--------------------------------------------------------------------------
| Framework Agnostic Functions
|--------------------------------------------------------------------------
|
| Simple functions not dependent to any framework and can be used in any PHP project.
|
*/

// returns an array like this: ['pending', 'in_process', 'completed']
StatusConst::all();

// returns a key-value array like this: ['pending' => 'Pending', 'in_process' => 'In Process', 'completed' => 'Completed']
StatusConst::options();

// returns a string like this: 'pending,in_process,completed'
StatusConst::asString();

// You can use any char you want as the glue for asString() function
// returns a string like this: 'pending|in_process|completed'
StatusConst::asString('|');

/*
|--------------------------------------------------------------------------
| Laravel Specific Functions
|--------------------------------------------------------------------------
|
| Functions to support [Laravel Collection](https://laravel.com/docs/collections) class.
| Laravel Collection is a class that provides a fluent, convenient wrapper for working with arrays of data.
|
*/

// returns a collection equivalent to: collect(['pending', 'in_process', 'completed'])
StatusConst::collect();

// returns a key-value collection equivalent to: collect(['pending' => 'Pending', 'in_process' => 'In Process', 'completed' => 'Completed'])
StatusConst::collectOptions();
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email [kevin.purwito@gmail.com](mailto:kevin.purwito@gmail.com) 
instead of using the issue tracker.

## Credits

- [Kevin Purwito](https://github.com/kevinpurwito)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## PHP Package Boilerplate

This package was generated using the [PHP Package Boilerplate](https://laravelpackageboilerplate.com)
by [Beyond Code](http://beyondco.de/) 
with some modifications inspired from [PHP Package Skeleton](https://github.com/spatie/package-skeleton-php) by [spatie](https://spatie.be/).
