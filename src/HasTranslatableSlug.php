<?php

namespace Esign\UnderscoreSluggable;

use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Localizable;
use LogicException;
use Spatie\Sluggable\HasSlug;

trait HasTranslatableSlug
{
    use HasSlug;
    use Localizable;

    protected function getLocalesForSlug(): Collection
    {
        return Collection::make($this->slugOptions->translatableLocales);
    }

    protected function addSlug(): void
    {
        $this->ensureUnderscoreTranslatable();
        $this->ensureValidSlugOptions();

        $this->getLocalesForSlug()->unique()->each(function ($locale) {
            $this->withLocale($locale, function () use ($locale) {
                // Temorarily change the 'slugField' of the SlugOptions
                // so following methods like 'generateNonUniqueSlug' and 'makeSlugUnique'
                // use the underscore-translatable column instead of the 'slugField'.
                $originalSlugField = $this->slugOptions->slugField;
                $translatableSlugField = $this->getTranslatableAttributeName($originalSlugField, $locale);
                $this->slugOptions->saveSlugsTo($translatableSlugField);

                $slug = $this->generateNonUniqueSlug();

                if ($this->slugOptions->generateUniqueSlugs) {
                    $slug = $this->makeSlugUnique($slug);
                }

                // revert the change for the next iteration
                $this->slugOptions->saveSlugsTo($originalSlugField);

                $this->setTranslation($originalSlugField, $locale, $slug);
            });
        });
    }

    protected function getSlugSourceStringFromCallable(): string
    {
        return call_user_func($this->slugOptions->generateSlugFrom, $this, app()->getLocale());
    }

    protected function ensureUnderscoreTranslatable(): void
    {
        if (! in_array(UnderscoreTranslatable::class, class_uses_recursive($this))) {
            throw new LogicException(sprintf(
                'The model must use the %s trait to use the %s trait.',
                UnderscoreTranslatable::class,
                HasTranslatableSlug::class
            ));
        }
    }

    public static function findBySlug(string $slug, array $columns = ['*']): ?self
    {
        $modelInstance = new static();
        $field = $modelInstance->getSlugOptions()->slugField;

        if (in_array(UnderscoreTranslatable::class, class_uses_recursive(static::class))) {
            $field = $modelInstance->getTranslatableAttributeName($field, app()->getLocale());
        }

        return static::query()->where($field, $slug)->first($columns);
    }
}
