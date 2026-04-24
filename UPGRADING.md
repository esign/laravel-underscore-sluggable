# Upgrading

## From v1 to v2

The `findBySlug` method now accepts an optional `additionalQuery` callback parameter, aligning behavior with the upstream Spatie API. This allows consumers to constrain slug lookups without writing custom query logic.

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
