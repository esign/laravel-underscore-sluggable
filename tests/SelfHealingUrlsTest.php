<?php

namespace Esign\UnderscoreSluggable\Tests;

use Esign\UnderscoreSluggable\Tests\Support\Models\DefaultRouteKeyPost;
use Esign\UnderscoreSluggable\Tests\Support\Models\SelfHealingTranslatablePost;
use Esign\UnderscoreSluggable\Tests\Support\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Sluggable\Actions\BuildSelfHealingRouteKeyAction;
use Spatie\Sluggable\Exceptions\StaleSelfHealingUrl;
use Spatie\Sluggable\Facades\SelfHealing;

final class SelfHealingUrlsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        SelfHealingTranslatablePost::$separator = '-';

        parent::tearDown();
    }

    #[Test]
    public function it_returns_the_slug_and_identifier_combination_as_the_route_key(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My first post');
        $post->setTranslation('title', 'nl', 'Mijn eerste post');
        $post->save();

        $this->assertSame("my-first-post-{$post->id}", $post->getRouteKey());
    }

    #[Test]
    public function it_falls_back_to_the_parent_route_key_when_self_healing_is_disabled(): void
    {
        $post = new DefaultRouteKeyPost();
        $post->title_en = 'My first post';
        $post->title_nl = 'Mijn eerste post';
        $post->save();

        $this->assertSame($post->getKey(), $post->getRouteKey());
    }

    #[Test]
    public function it_resolves_a_model_by_identifier_when_the_slug_matches_the_canonical_value(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My first post');
        $post->setTranslation('title', 'nl', 'Mijn eerste post');
        $post->save();

        $resolved = (new SelfHealingTranslatablePost())->resolveRouteBinding($post->getRouteKey());

        $this->assertNotNull($resolved);
        $this->assertSame($post->id, $resolved->id);
    }

    #[Test]
    public function it_returns_null_when_the_identifier_cannot_be_found(): void
    {
        $this->assertNull((new SelfHealingTranslatablePost())->resolveRouteBinding('missing-999'));
    }

    #[Test]
    public function it_returns_null_when_the_value_contains_no_identifier_separator(): void
    {
        $this->assertNull((new SelfHealingTranslatablePost())->resolveRouteBinding('noseparator'));
    }

    #[Test]
    public function it_throws_a_stale_self_healing_url_exception_when_the_slug_is_stale(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My updated post');
        $post->setTranslation('title', 'nl', 'Mijn bijgewerkte post');
        $post->save();
        $staleRouteKey = "my-first-post-{$post->id}";

        try {
            (new SelfHealingTranslatablePost())->resolveRouteBinding($staleRouteKey);
        } catch (StaleSelfHealingUrl $exception) {
            $this->assertSame($post->id, $exception->model->id);
            $this->assertSame($staleRouteKey, $exception->staleRouteKey);

            return;
        }

        $this->fail('Expected a stale self-healing URL exception to be thrown.');
    }

    #[Test]
    public function it_respects_a_custom_separator(): void
    {
        SelfHealingTranslatablePost::$separator = '--';

        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My first post');
        $post->setTranslation('title', 'nl', 'Mijn eerste post');
        $post->save();

        $this->assertSame("my-first-post--{$post->id}", $post->getRouteKey());

        $resolved = (new SelfHealingTranslatablePost())->resolveRouteBinding($post->getRouteKey());

        $this->assertNotNull($resolved);
        $this->assertSame($post->id, $resolved->id);
    }

    #[Test]
    public function it_redirects_with_a_301_when_an_implicit_route_binding_encounters_a_stale_slug(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My updated post');
        $post->setTranslation('title', 'nl', 'Mijn bijgewerkte post');
        $post->save();

        Route::get('/posts/{post}', fn (SelfHealingTranslatablePost $post) => $post->title)
            ->middleware(SubstituteBindings::class);

        $response = $this->get("/posts/my-first-post-{$post->id}");

        $response->assertStatus(301);
        $response->assertRedirect("/posts/my-updated-post-{$post->id}");
    }

    #[Test]
    public function it_responds_with_200_when_the_url_is_already_canonical(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My updated post');
        $post->setTranslation('title', 'nl', 'Mijn bijgewerkte post');
        $post->save();

        Route::get('/posts/{post}', fn (SelfHealingTranslatablePost $post) => $post->title)
            ->middleware(SubstituteBindings::class);

        $response = $this->get("/posts/my-updated-post-{$post->id}");

        $response->assertOk();
        $response->assertSee('My updated post');
    }

    #[Test]
    public function it_invokes_a_custom_handler_registered_through_the_sluggable_facade(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My updated post');
        $post->setTranslation('title', 'nl', 'Mijn bijgewerkte post');
        $post->save();

        SelfHealing::onStaleSelfHealingUrl(function (Model $model, string $staleRouteKey, Request $request) {
            return response("stale:{$staleRouteKey}:canonical:{$model->getRouteKey()}", 418);
        });

        Route::get('/posts/{post}', fn (SelfHealingTranslatablePost $post) => $post->title)
            ->middleware(SubstituteBindings::class);

        $response = $this->get("/posts/my-first-post-{$post->id}");

        $response->assertStatus(418);
        $response->assertSee("stale:my-first-post-{$post->id}:canonical:my-updated-post-{$post->id}");
    }

    #[Test]
    public function it_uses_a_custom_action_class_registered_in_config(): void
    {
        config()->set('sluggable.actions.build_self_healing_route_key', UppercaseSlugRouteKeyAction::class);

        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My first post');
        $post->setTranslation('title', 'nl', 'Mijn eerste post');
        $post->save();

        $this->assertSame("MY-FIRST-POST-{$post->id}", $post->getRouteKey());
    }

    #[Test]
    public function it_builds_a_self_healing_route_key_per_locale_on_translatable_models(): void
    {
        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My first post');
        $post->setTranslation('title', 'nl', 'Mijn eerste post');
        $post->save();

        App::setLocale('en');
        $this->assertSame("my-first-post-{$post->id}", $post->getRouteKey());

        App::setLocale('nl');
        $this->assertSame("mijn-eerste-post-{$post->id}", $post->getRouteKey());
    }

    #[Test]
    public function it_redirects_stale_translatable_urls_to_the_current_locale_canonical_url(): void
    {
        App::setLocale('en');

        $post = new SelfHealingTranslatablePost();
        $post->setTranslation('title', 'en', 'My updated post');
        $post->setTranslation('title', 'nl', 'Mijn bijgewerkte post');
        $post->save();

        Route::get('/posts/{post}', fn (SelfHealingTranslatablePost $post) => $post->getTranslation('title', 'en'))
            ->middleware(SubstituteBindings::class);

        $response = $this->get("/posts/my-first-post-{$post->id}");

        $response->assertStatus(301);
        $response->assertRedirect("/posts/my-updated-post-{$post->id}");
    }
}

class UppercaseSlugRouteKeyAction extends BuildSelfHealingRouteKeyAction
{
    public function execute(string $slug, int|string $identifier, string $separator): string
    {
        return parent::execute(strtoupper($slug), $identifier, $separator);
    }
}