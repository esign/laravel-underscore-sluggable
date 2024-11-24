# Generate slugs for UnderscoreTranslatable models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-underscore-sluggable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-underscore-sluggable)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-underscore-sluggable.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-underscore-sluggable)
![GitHub Actions](https://github.com/esign/laravel-underscore-sluggable/actions/workflows/main.yml/badge.svg)

This package adds support for [`spatie/laravel-sluggable`](https://github.com/spatie/laravel-sluggable) package to models that use the `UnderscoreTranslatable` trait from the [`esign/laravel-underscore-translatable`](https://github.com/esign/laravel-underscore-translatable) package.

## Installation
You can install the package via composer:

```bash
composer require esign/laravel-underscore-sluggable
```

## Usage
To support slug generation for models that use the `UnderscoreTranslatable` trait, you may add the `HasTranslatableSlug` trait to your models.
Next up, you should define the `getSlugOptions` method on your model, which should be created using the `createWithLocales` method.

```php
namespace App\Models;

use Esign\UnderscoreSluggable\HasTranslatableSlug;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use UnderscoreTranslatable;
    use HasTranslatableSlug;

    public $translatable = [
        'title',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
```

### Generating a slug from a callback
You may also generate a slug from a callback by passing a closure to the `generateSlugsFrom` method.
This callback will receive the model instance and the current locale as arguments:

```php
namespace App\Models;

use Esign\UnderscoreSluggable\HasTranslatableSlug;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use UnderscoreTranslatable;
    use HasTranslatableSlug;

    public $translatable = [
        'title',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom(function (Model $model, string $locale) {
                return $model->getTranslation('title', $locale) . '-' . $model->id;
            })
            ->saveSlugsTo('slug');
    }
}
```

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
