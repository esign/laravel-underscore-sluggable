# Upgrading

## From v1 to v2

### Dependency changes

- **PHP 8.3** is now the minimum required version. PHP 8.1 and 8.2 are no longer supported.
- **Laravel 12** is now the minimum required version. Laravel 11 is no longer supported.
- **`spatie/laravel-sluggable` v4** is now required. Please refer to the [spatie/laravel-sluggable upgrade guide](https://github.com/spatie/laravel-sluggable/blob/main/UPGRADING.md) for details on upstream breaking changes.

### Method changes

- The `findBySlug` method signature has been updated to accept an optional third argument `?callable $additionalQuery`. Existing calls remain backward compatible.

#### Migration

Existing calls to `findBySlug` continue to work without any changes:

```php
Post::findBySlug('my-first-post');
Post::findBySlug('my-first-post', ['id', 'slug_en']);
```

To further scope the lookup, you may now pass an additional query callback as the third argument:

```php
Post::findBySlug('my-first-post', ['*'], function ($query) {
    $query->where('status', 'published');
});
```

See the [Finding a model by slug](README.md#finding-a-model-by-slug) section in the README for more details.
