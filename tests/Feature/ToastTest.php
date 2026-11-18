<?php

use App\Models\Review;
use App\Support\Toast;
use Symfony\Component\Finder\Finder;

use function Pest\Laravel\actingAs;

/*
 * Notifications in the admin panel and on the website: one way to send them
 * (App\Support\Toast), one way to show them (resources/js/lib/toast.ts), and
 * always at the top of the screen.
 */

it('tells the admin what happened, in their language, on the next page', function () {
    $review = Review::factory()->create();

    actingAs(staffMember('admin'))
        ->delete("/admin/community/reviews/{$review->id}")
        ->assertRedirect()
        ->assertSessionHas(Toast::SESSION_KEY, ['type' => Toast::SUCCESS, 'message' => 'Recenzia a fost ștearsă.']);
});

it('sends every notification through the one Toast class', function () {
    $offenders = [];

    foreach (Finder::create()->files()->in(app_path())->name('*.php') as $file) {
        if (preg_match('/->with\(\s*[\'"](success|error|status|message)[\'"]/', $file->getContents()) === 1) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([]);
});

it('shows every toast at the top of the screen, through the one helper', function () {
    expect(file_get_contents(resource_path('js/components/ui/sonner.tsx')))->toContain('position="top-center"');

    $direct = [];

    foreach (Finder::create()->files()->in(resource_path('js'))->name(['*.ts', '*.tsx']) as $file) {
        $path = $file->getRelativePathname();

        if (! in_array($path, ['lib/toast.ts', 'components/ui/sonner.tsx'], true) && str_contains($file->getContents(), "from 'sonner'")) {
            $direct[] = $path;
        }
    }

    expect($direct)->toBe([]);
});
