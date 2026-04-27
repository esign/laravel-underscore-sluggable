<?php

namespace Esign\UnderscoreSluggable\Tests\Support\Models;

use Esign\UnderscoreSluggable\HasTranslatableSlug;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

class SelfHealingTranslatablePost extends Model
{
    use UnderscoreTranslatable;
    use HasTranslatableSlug;

    public static string $separator = '-';

    protected $table = 'posts';

    public $timestamps = false;

    protected $guarded = [];

    public $translatable = [
        'title',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->selfHealing(static::$separator);
    }
}