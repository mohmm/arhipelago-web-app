<?php

/**
 * The admin panel's menu is pure client-side React — Inertia ships it as a prop
 * tree, so no HTTP test can see a single one of its labels. These tests read
 * the component instead.
 *
 * They exist because the failures they guard against are silent: a duplicated
 * label, a link to a screen that does not exist, or a link shown to somebody
 * whose role the screen then answers with a 403.
 */
$sidebar = fn (): string => (string) file_get_contents(resource_path('js/components/app-sidebar.tsx'));

/**
 * Every group heading the sidebar passes to `NavMain`.
 *
 * @return list<string>
 */
function sidebarGroupLabels(string $source): array
{
    preg_match_all('/label=\{t\(\s*\'((?:\\\\.|[^\'])*)\'/', $source, $matches);

    return array_values(array_unique($matches[1]));
}

/**
 * Every link label in the sidebar, in the order it is declared.
 *
 * @return list<string>
 */
function sidebarItemLabels(string $source): array
{
    preg_match_all('/title:\s*t\(\s*\'((?:\\\\.|[^\'])*)\'/', $source, $matches);

    return $matches[1];
}

/**
 * Every `/admin/...` path the sidebar links to, in the order it is declared.
 *
 * @return list<string>
 */
function sidebarAdminHrefs(string $source): array
{
    preg_match_all("/href:\s*url\('(\/admin\/[^']*)'\)/", $source, $matches);

    return array_values(array_unique($matches[1]));
}

it('never shows the same label on two different links', function () use ($sidebar) {
    $labels = sidebarItemLabels($sidebar());

    $duplicated = array_values(array_unique(array_diff_assoc($labels, array_unique($labels))));

    expect($duplicated)->toBe([])
        ->and(count($labels))->toBeGreaterThan(5);
});

it('keeps the menu short', function () use ($sidebar) {
    // The panel was rebuilt around the app's design so a new member of the
    // team can find everything at a glance. A ninth link is a decision to make
    // deliberately, not one to slip in.
    expect(count(sidebarItemLabels($sidebar())))->toBeLessThanOrEqual(8);
});

it('translates every group heading', function () use ($sidebar) {
    $romanian = json_decode((string) file_get_contents(lang_path('ro.json')), true, flags: JSON_THROW_ON_ERROR);
    $english = json_decode((string) file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

    $labels = sidebarGroupLabels($sidebar());

    expect($labels)->not->toBe([]);

    foreach ($labels as $label) {
        expect($romanian)->toHaveKey($label)
            ->and($english)->toHaveKey($label);
    }
});

it('leaves the dashboard group unlabelled', function () use ($sidebar) {
    // One item, above every group. A heading over it would repeat the word
    // already written inside it.
    expect($sidebar())->toContain('<NavMain items={overview} />');
});

it('groups the menu as the app is organised: content, community, administration', function () use ($sidebar) {
    $source = $sidebar();

    $order = array_map(
        fn (string $needle): int|false => mb_strpos($source, $needle),
        ["label={t('Content')}", "label={t('Community')}", "label={t('Administration')}"],
    );

    expect($order)->not->toContain(false)
        ->and($order)->toBe(array_values(collect($order)->sort()->all()));
});

it('never links to a screen that does not exist', function () use ($sidebar) {
    // A typo in an href renders as a perfectly ordinary link and fails only
    // when somebody clicks it.
    $registered = collect(Route::getRoutes()->getRoutesByMethod()['GET'] ?? [])
        ->keys()
        ->map(fn (string $uri): string => '/'.ltrim($uri, '/'))
        ->all();

    $hrefs = sidebarAdminHrefs($sidebar());

    expect($hrefs)->toHaveCount(8)
        ->and(array_values(array_diff($hrefs, $registered)))->toBe([]);
});

it('shows each link only to the roles its screen lets in', function (string $href, string $permission) use ($sidebar) {
    // A link gated on less than its route asks for shows somebody a screen
    // that answers them with a 403.
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($candidate): bool => '/'.$candidate->uri() == $href && in_array('GET', $candidate->methods(), true));

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain("permission:{$permission}");

    $gate = mb_strpos($sidebar(), "can('{$permission}')");
    $link = mb_strpos($sidebar(), "url('{$href}')");

    expect($gate)->not->toBeFalse()
        ->and($link)->toBeGreaterThan($gate);
})->with([
    ['/admin/points-of-interest', 'content.view'],
    ['/admin/experiences', 'content.view'],
    ['/admin/community/posts', 'community.view'],
    ['/admin/maya', 'settings.view'],
    ['/admin/legal-documents', 'settings.view'],
]);
