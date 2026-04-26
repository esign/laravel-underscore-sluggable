<?php

namespace Esign\UnderscoreSluggable\Tests\Support\Models;

use Esign\UnderscoreSluggable\HasTranslatableSlug;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

class DefaultRouteKeyPost extends Model
{
    use UnderscoreTranslatable;
    use HasTranslatableSlug;

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
            ->saveSlugsTo('slug');
    }
}