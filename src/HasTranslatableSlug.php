<?php

namespace Esign\UnderscoreSluggable;

use Esign\UnderscoreTranslatable\UnderscoreTranslatable;
use Illuminate\Support\Collection;
use LogicException;
use Spatie\Sluggable\Actions\GenerateSlugAction;
use Spatie\Sluggable\HasTranslatableSlug as BaseHasTranslatableSlug;
use Spatie\Sluggable\Support\Config;

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

        $action = Config::getAction('generate_slug', GenerateSlugAction::class);
        $action->ensureValidOptions($this->slugOptions);

        $originalSlugField = $this->slugOptions->slugField;

        $this->getLocalesForSlug()->unique()->each(function ($locale) use ($action, $originalSlugField) {
            if ($this->slugOptions->preventOverwrite) {
                if (filled($this->getTranslation($originalSlugField, $locale, false))) {
                    return;
                }
            }

            $this->withLocale($locale, function () use ($locale, $action, $originalSlugField) {
                // Temorarily change the 'slugField' of the SlugOptions
                // so following methods like 'generateNonUniqueSlug' and 'makeSlugUnique'
                // use the underscore-translatable column instead of the 'slugField'.
                $translatableSlugField = $this->getTranslatableAttributeName($originalSlugField, $locale);
                $this->slugOptions->saveSlugsTo($translatableSlugField);

                $slug = $this->generateNonUniqueSlug();

                if ($this->slugOptions->generateUniqueSlugs) {
                    $slug = $action->makeUnique($slug, $this, $this->slugOptions);
                }

                // revert the change for the next iteration
                $this->slugOptions->saveSlugsTo($originalSlugField);

                $this->setTranslation($originalSlugField, $locale, $slug);
            });
        });
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
