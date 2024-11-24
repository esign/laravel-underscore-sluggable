<?php

namespace Esign\UnderscoreSluggable\Tests\Support\Models;

use Esign\UnderscoreSluggable\HasTranslatableSlug;
use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use UnderscoreTranslatable;
    use HasTranslatableSlug;

    public $timestamps = false;
    protected $guarded = [];
    public $translatable = [
        'title',
        'slug',
        'country',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return $this->slugOptions ?? SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function setSlugOptions(SlugOptions $slugOptions): self
    {
        $this->slugOptions = $slugOptions;

        return $this;
    }
}
