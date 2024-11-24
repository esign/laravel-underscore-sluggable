# Generate slugs for esign/laravel-underscore-translatable using spatie/laravel-sluggable.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-underscore-sluggable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-underscore-sluggable)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-underscore-sluggable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-underscore-sluggable)
![GitHub Actions](https://github.com/esign/laravel-underscore-sluggable/actions/workflows/main.yml/badge.svg)

A short intro about the package.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-underscore-sluggable
```

The package will automatically register a service provider.

Next up, you can publish the configuration file:
```bash
php artisan vendor:publish --provider="Esign\UnderscoreSluggable\UnderscoreSluggableServiceProvider" --tag="config"
```

## Usage

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
