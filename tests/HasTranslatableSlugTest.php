<?php

namespace Esign\UnderscoreSluggable\Tests;

use Esign\UnderscoreSluggable\Tests\Support\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Sluggable\SlugOptions;

class HasTranslatableSlugTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function it_can_generate_a_slug_for_multiple_locales()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $this->assertEquals('my-first-post', $post->slug_en);
        $this->assertEquals('mijn-eerste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_update_one_of_the_slugs()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $post->update(['title_en' => 'My updated post']);

        $this->assertEquals('my-updated-post', $post->slug_en);
        $this->assertEquals('mijn-eerste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_make_the_slug_unique_for_multiple_locales()
    {
        $postA = new Post();
        $postA->title_en = 'My first post';
        $postA->title_nl = 'Mijn eerste post';
        $postA->save();

        $postB = new Post();
        $postB->title_en = 'My first post';
        $postB->title_nl = 'Mijn eerste post';
        $postB->save();

        $this->assertEquals('my-first-post-1', $postB->slug_en);
        $this->assertEquals('mijn-eerste-post-1', $postB->slug_nl);
    }

    #[Test]
    public function it_can_generate_a_slug_based_on_multiple_fields()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->country_en = 'Belgium';
        $post->country_nl = 'België';
        $post->setSlugOptions(
            SlugOptions::createWithLocales(['en', 'nl'])
                ->generateSlugsFrom(['title', 'country'])
                ->saveSlugsTo('slug')
        );
        $post->save();

        $this->assertEquals('my-first-post-belgium', $post->slug_en);
        $this->assertEquals('mijn-eerste-post-belgie', $post->slug_nl);
    }

    /** @test */
    public function it_can_generate_a_slug_based_on_fields_that_are_not_translatable()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->year = '2025';
        $post->setSlugOptions(
            SlugOptions::createWithLocales(['en', 'nl'])
                ->generateSlugsFrom(['year'])
                ->saveSlugsTo('slug')
        );
        $post->save();

        $this->assertEquals('2025', $post->slug_en);
        $this->assertEquals('2025', $post->slug_nl);
    }

    #[Test]
    public function it_can_generate_a_slug_based_on_fields_that_are_not_all_translatable()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->year = '2025';
        $post->setSlugOptions(
            SlugOptions::createWithLocales(['en', 'nl'])
                ->generateSlugsFrom(['title', 'year'])
                ->saveSlugsTo('slug')
        );
        $post->save();

        $this->assertEquals('my-first-post-2025', $post->slug_en);
        $this->assertEquals('mijn-eerste-post-2025', $post->slug_nl);
    }

    #[Test]
    public function it_can_generate_a_slug_using_a_callback()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->country_en = 'Belgium';
        $post->country_nl = 'België';
        $post->setSlugOptions(
            SlugOptions::createWithLocales(['en', 'nl'])
                ->generateSlugsFrom(function (Post $post, string $locale) {
                    return implode(' ', [
                        $post->getTranslation('title', $locale),
                        $post->getTranslation('country', $locale),
                    ]);
                })
                ->saveSlugsTo('slug')
        );
        $post->save();

        $this->assertEquals('my-first-post-belgium', $post->slug_en);
        $this->assertEquals('mijn-eerste-post-belgie', $post->slug_nl);
    }

    #[Test]
    public function it_can_handle_overwrites_when_creating_a_model()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->slug_en = 'my-updated-post';
        $post->slug_nl = 'mijn-aangepaste-post';
        $post->save();

        $this->assertEquals('my-updated-post', $post->slug_en);
        $this->assertEquals('mijn-aangepaste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_handle_overwrites_when_updating_a_model()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $post->slug_en = 'my-updated-post';
        $post->slug_nl = 'mijn-aangepaste-post';
        $post->save();

        $this->assertEquals('my-updated-post', $post->slug_en);
        $this->assertEquals('mijn-aangepaste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_handle_overwrites_for_one_item_when_updating_a_post()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $post->slug_en = 'my-updated-post';
        $post->save();

        $this->assertEquals('my-updated-post', $post->slug_en);
        $this->assertEquals('mijn-eerste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_handle_overwrites_for_one_item_when_updating_a_post_with_custom_slugs()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->slug_en = 'my-updated-post';
        $post->save();

        $post->slug_nl = 'mijn-aangepaste-post';
        $post->save();

        $this->assertEquals('my-first-post', $post->slug_en);
        $this->assertEquals('mijn-aangepaste-post', $post->slug_nl);
    }

    #[Test]
    public function it_can_handle_duplicates_when_overwriting_a_slug()
    {
        $postA = new Post();
        $postA->title_en = 'My first post';
        $postA->title_nl = 'Mijn eerste post';
        $postA->save();

        $postB = new Post();
        $postB->title_en = 'My second post';
        $postB->title_nl = 'Mijn tweede post';
        $postB->save();

        $postB->slug_en = 'my-first-post';
        $postB->slug_nl = 'mijn-eerste-post';
        $postB->save();

        $this->assertEquals('my-first-post-1', $postB->slug_en);
        $this->assertEquals('mijn-eerste-post-1', $postB->slug_nl);
    }

    /** @test */
    public function it_can_handle_duplicates_when_updating_a_model_for_fields_that_are_not_translatable()
    {
        $slugOptions = SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom(['year'])
            ->saveSlugsTo('slug');

        $postA = new Post();
        $postA->setSlugOptions($slugOptions);
        $postA->title_en = 'My first post';
        $postA->title_nl = 'Mijn eerste post';
        $postA->year = '2025';
        $postA->save();

        $postB = new Post();
        $postB->setSlugOptions($slugOptions);
        $postB->title_en = 'My second post';
        $postB->title_nl = 'Mijn tweede post';
        $postB->year = '2025';
        $postB->save();

        $this->assertEquals('2025', $postA->slug_en);
        $this->assertEquals('2025', $postA->slug_nl);
        $this->assertEquals('2025-1', $postB->slug_en);
        $this->assertEquals('2025-1', $postB->slug_nl);
    }

    /** @test */
    public function it_can_find_models_using_find_by_slug()
    {
        $post = new Post();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $foundPost = Post::findBySlug('my-first-post');

        $this->assertTrue($foundPost->is($post));
    }
}
