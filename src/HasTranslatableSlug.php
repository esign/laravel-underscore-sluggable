<?php

namespace Esign\UnderscoreSluggable;

use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Support\Collection;
use LogicException;
use Spatie\Sluggable\HasTranslatableSlug as BaseHasTranslatableSlug;

trait HasTranslatableSlug
{
    use BaseHasTranslatableSlug;

    protected function getLocalesForSlug(): Collection
    {
        return Collection::make($this->slugOptions->translatableLocales);
    }

    protected function addSlug(): void
    {
        $this->ensureUnderscoreTranslatable();

        $action = $this->generateSlugAction();
        $action->ensureValidOptions($this->slugOptions);

        $slugField = $this->slugOptions->slugField;

        $this->getLocalesForSlug()->unique()->each(function ($locale) use ($action, $slugField) {
            if ($this->slugOptions->preventOverwrite && filled($this->getTranslation($slugField, $locale, false))) {
                return;
            }

            $this->withLocale($locale, function () use ($locale, $action, $slugField) {
                $slug = $this->generateNonUniqueSlug();

                if ($this->slugOptions->generateUniqueSlugs) {
                    $localeOptions = clone $this->slugOptions;
                    $localeOptions->saveSlugsTo($this->getTranslatableAttributeName($slugField, $locale));
                    $slug = $action->makeUnique($slug, $this, $localeOptions);
                }

                $this->setTranslation($slugField, $locale, $slug);
            });
        });
    }

    protected function getOriginalSourceString(): string
    {
        return $this->buildTranslatableSourceString(function (string $fieldName): string {
            if ($this->isTranslatableAttribute($fieldName)) {
                return (string) $this->getOriginal(
                    $this->getTranslatableAttributeName($fieldName, $this->getLocale()),
                    ''
                );
            }

            return (string) $this->getOriginal($fieldName, '');
        });
    }

    protected function hasCustomSlugBeenUsed(): bool
    {
        $attributeName = $this->getTranslatableAttributeName($this->slugOptions->slugField, $this->getLocale());

        return $this->getOriginal($attributeName) !== $this->getAttribute($attributeName);
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

    public function getLocalizedRouteKey(string $locale): mixed
    {
        $this->ensureUnderscoreTranslatable();

        $originalLocale = $this->translationLocale;

        try {
            $this->setLocale($locale);

            return $this->getRouteKey();
        } finally {
            $this->setLocale($originalLocale);
        }
    }

    public static function findBySlug(string $slug, array $columns = ['*'], ?callable $additionalQuery = null): ?self
    {
        $modelInstance = new static();
        $field = $modelInstance->getSlugOptions()->slugField;

        if (in_array(UnderscoreTranslatable::class, class_uses_recursive(static::class))) {
            $field = $modelInstance->getTranslatableAttributeName($field, app()->getLocale());
        }

        $query = static::query()->where($field, $slug);

        if ($additionalQuery !== null) {
            $additionalQuery($query);
        }

        return $query->first($columns);
    }
}
