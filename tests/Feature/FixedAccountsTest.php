<?php

use App\Models\User;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

/*
 * An app can have a fixed set of accounts, by design (config/accounts.php):
 * user1@<domain> to user<limit>@<domain>, made by `accounts:create`. Then
 * nobody can sign up, and the app hides its sign-up button.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(LegalDocumentSeeder::class);
});

it('lets anyone sign up when the app has no fixed accounts', function () {
    config()->set('accounts.limit', null);

    getJson('/api/config')->assertJsonPath('data.sign_up', true);
});

it('closes sign-up when the app has a fixed set of accounts', function () {
    config()->set(['accounts.limit' => 3, 'accounts.domain' => 'example.test']);

    getJson('/api/config')->assertJsonPath('data.sign_up', false);

    postJson('/api/register', [
        'first_name' => 'Ana',
        'last_name' => 'Marin',
        'email' => 'ana@example.com',
        'password' => 'drumuri-frumoase-1',
    ], ['Accept-Language' => 'en'])
        ->assertForbidden()
        ->assertJsonPath('message', 'New accounts are given out by '.config('app.name').'. Sign in with the one you received.');

    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
});

it('makes the fixed accounts once, with their passwords in a private file', function () {
    Storage::fake('local');
    config()->set(['accounts.limit' => 3, 'accounts.domain' => 'example.test']);

    artisan('accounts:create')->assertSuccessful();

    $files = Storage::disk('local')->files('accounts');
    $lines = explode("\n", trim(Storage::disk('local')->get($files[0])));

    expect($files)->toHaveCount(1)
        ->and(Storage::disk('local')->getVisibility($files[0]))->toBe('private')
        ->and(array_shift($lines))->toBe('email,password')
        ->and($lines)->toHaveCount(3);

    foreach ($lines as $index => $line) {
        [$email, $password] = explode(',', $line);
        $user = User::query()->where('email', $email)->sole();

        expect($email)->toBe('user'.($index + 1).'@example.test')
            ->and(Hash::check($password, $user->password))->toBeTrue()
            ->and($user->hasRole('customer'))->toBeTrue()
            ->and($user->hasVerifiedEmail())->toBeTrue();
    }

    // Running it again changes nothing: no new account, no new password.
    artisan('accounts:create')->assertSuccessful();

    expect(User::query()->where('email', 'like', '%@example.test')->count())->toBe(3)
        ->and(Storage::disk('local')->files('accounts'))->toHaveCount(1);
});

it('makes no accounts when anyone can sign up', function () {
    config()->set('accounts.limit', null);

    artisan('accounts:create')->assertFailed();

    $this->assertDatabaseMissing('users', ['email' => 'user1@example.test']);
});
