<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubNamedDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Feature tests for the config-driven sidebar menu.
 *
 * The sidebar is rendered as part of the main layout (here via the dashboard
 * page). It is driven entirely by config('bpadmin.menu'): only the listed
 * entities are shown, in the declared order and grouping — with no
 * auto-discovery dump when the menu is set.
 */
final class SidebarMenuTest extends TestCase
{
    private function actAsAdmin(): void
    {
        $user = new \Illuminate\Foundation\Auth\User();
        $user->id = 1;
        $user->name = 'Test Admin';
        $user->email = 'admin@example.com';
        $this->actingAs($user);
    }

    private function renderSidebar(): string
    {
        $this->actAsAdmin();

        // Render the sidebar partial in isolation. Rendering the whole page
        // would also pull in the command palette (which enumerates every
        // registered entity for search) — that would defeat the "only menu
        // entities are visible" assertions.
        return view('bpadmin::components.sidebar')->render();
    }

    public function test_menu_renders_only_configured_entities_in_order(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));
        $registry->register(new StubNamedDefinition('authors'));
        $registry->register(new StubNamedDefinition('users'));
        // Registered but intentionally absent from the menu config.
        $registry->register(new StubNamedDefinition('secret_embed'));

        config()->set('bpadmin.menu', [
            ['label' => 'Catalog', 'icon' => 'book',  'entities' => ['books', 'authors']],
            ['label' => 'Access',  'icon' => 'users', 'entities' => ['users']],
        ]);

        $body = $this->renderSidebar();

        // Group headers and the labels of listed entities are present.
        self::assertStringContainsString('Catalog', $body);
        self::assertStringContainsString('Access', $body);
        self::assertStringContainsString('Books', $body);
        self::assertStringContainsString('Authors', $body);
        self::assertStringContainsString('Users', $body);

        // A registered entity NOT listed in the menu must not appear. Assert on
        // the human label ('Secret Embed') — the raw name would false-match
        // unrelated markup, and 'ghost' even collides with the bp-btn-ghost class.
        self::assertStringNotContainsString('Secret Embed', $body);

        // Declared order is preserved: Catalog group before Access group,
        // and Books before Authors within the first group.
        self::assertLessThan(strpos($body, 'Access'), strpos($body, 'Catalog'));
        self::assertLessThan(strpos($body, 'Authors'), strpos($body, 'Books'));

        // The configured group icons are rendered (book + users glyph paths).
        self::assertStringContainsString('M12 6.253v13', $body);
        self::assertStringContainsString('M12 4.354a4 4 0', $body);
    }

    public function test_unregistered_entity_names_are_skipped(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));

        config()->set('bpadmin.menu', [
            // 'ghost' is never registered — it must be silently skipped, and
            // the group still renders because 'books' resolves.
            ['label' => 'Catalog', 'icon' => 'book', 'entities' => ['ghost', 'books']],
        ]);

        $body = $this->renderSidebar();

        self::assertStringContainsString('Catalog', $body);
        self::assertStringContainsString('Books', $body);
        // 'Ghost' is the headline label the skipped entity would have rendered.
        self::assertStringNotContainsString('Ghost', $body);
    }

    public function test_empty_menu_falls_back_to_all_registered_entities(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));
        $registry->register(new StubNamedDefinition('authors'));

        config()->set('bpadmin.menu', []);

        $body = $this->renderSidebar();

        // Zero-config fallback lists every registered entity, ungrouped.
        self::assertStringContainsString('Books', $body);
        self::assertStringContainsString('Authors', $body);
    }

    public function test_item_icon_overrides_group_icon(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));
        $registry->register(new StubNamedDefinition('authors'));

        config()->set('bpadmin.menu', [
            ['label' => 'Catalog', 'icon' => 'book', 'entities' => [
                'books',                                    // inherits group icon (book)
                ['entity' => 'authors', 'icon' => 'user'],  // own icon
            ]],
        ]);

        $body = $this->renderSidebar();

        // Group header + the inherited 'books' item render the 'book' glyph.
        self::assertStringContainsString('M12 6.253v13', $body);
        // The 'authors' item overrides with the 'user' glyph.
        self::assertStringContainsString('M16 7a4 4 0 11-8 0', $body);
    }

    public function test_custom_config_icon_renders_in_the_menu(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));

        // An app-defined icon not in the bundled IconSet, referenced by the menu.
        config()->set('bpadmin.icons', [
            'sparkle' => '<path d="M2 2l9 9"/>',
        ]);
        config()->set('bpadmin.menu', [
            ['label' => 'Catalog', 'icon' => 'sparkle', 'entities' => ['books']],
        ]);

        $body = $this->renderSidebar();

        // The custom icon markup is rendered end-to-end (group header + item).
        self::assertStringContainsString('<path d="M2 2l9 9"/>', $body);
        self::assertSame(2, substr_count($body, '<path d="M2 2l9 9"/>'));
    }

    public function test_custom_per_item_icon_can_use_a_config_defined_icon(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('books'));

        config()->set('bpadmin.icons', [
            'diamond' => '<path d="M6 3h12l4 6-10 12L2 9z"/>',
        ]);
        config()->set('bpadmin.menu', [
            ['label' => 'Catalog', 'icon' => 'book', 'entities' => [
                ['entity' => 'books', 'icon' => 'diamond'],
            ]],
        ]);

        $body = $this->renderSidebar();

        // Group header keeps 'book'; the item uses the custom 'diamond' icon.
        self::assertStringContainsString('M12 6.253v13', $body);
        self::assertStringContainsString('<path d="M6 3h12l4 6-10 12L2 9z"/>', $body);
    }

    public function test_item_label_override_replaces_the_entity_label(): void
    {
        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('users'));

        config()->set('bpadmin.menu', [
            ['label' => 'Access', 'icon' => 'users', 'entities' => [
                ['entity' => 'users', 'icon' => 'user', 'label' => 'Administrators'],
            ]],
        ]);

        $body = $this->renderSidebar();

        // The explicit label is shown instead of the entity's headline ('Users').
        self::assertStringContainsString('Administrators', $body);
    }

    public function test_group_and_item_labels_are_localized(): void
    {
        // A label that matches an existing translation key is localized; the
        // value can be a literal or a key — both supported by bp_menu_label().
        $this->app->make('translator')->addLines([
            'menu.access' => 'Localized Access',
            'menu.admins' => 'Localized Admins',
        ], 'en');

        $registry = $this->app->make(EntityDefinitionRegistry::class);
        $registry->register(new StubNamedDefinition('users'));

        config()->set('bpadmin.menu', [
            ['label' => 'menu.access', 'icon' => 'users', 'entities' => [
                ['entity' => 'users', 'label' => 'menu.admins'],
            ]],
        ]);

        $body = $this->renderSidebar();

        // Both the group label and the item label are translated...
        self::assertStringContainsString('Localized Access', $body);
        self::assertStringContainsString('Localized Admins', $body);
        // ...and the raw translation keys never leak into the markup.
        self::assertStringNotContainsString('menu.access', $body);
        self::assertStringNotContainsString('menu.admins', $body);
    }
}
