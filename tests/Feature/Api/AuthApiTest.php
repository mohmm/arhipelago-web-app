<?php

use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    seedRoles();
    LegalDocument::factory()->terms()->create();
    LegalDocument::factory()->privacy()->create();
});

it('signs a traveller up, records the accepted documents and returns a token', function () {
    Notification::fake();

    $response = $this->postJson('/api/register', [
        'first_name' => 'Ana',
        'last_name' => 'Marin',
        'email' => 'ana@example.com',
        'password' => 'drumuri-frumoase-1',
    ], ['Accept-Language' => 'en']);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'ana@example.com')
        ->assertJsonPath('data.initials', 'AM')
        ->assertJsonPath('data.locale', 'en')
        ->assertJsonStructure(['data' => ['id', 'name', 'stats'], 'token']);

    $user = User::where('email', 'ana@example.com')->firstOrFail();

    expect($user->hasRole('customer'))->toBeTrue()
        ->and($user->canAccessAdminPanel())->toBeFalse()
        ->and(LegalAcceptance::where('user_id', $user->id)->pluck('slug')->sort()->values()->all())
        ->toBe([LegalDocument::PRIVACY, LegalDocument::TERMS]);

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('refuses an address that already has an account', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->postJson('/api/register', [
        'first_name' => 'Ana',
        'last_name' => 'Marin',
        'email' => 'ana@example.com',
        'password' => 'drumuri-frumoase-1',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('signs a traveller in with the right password only', function () {
    $user = User::factory()->create(['email' => 'ana@example.com']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonStructure(['token']);

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'wrong'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('pauses sign-in after five wrong passwords', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'wrong']);
    }

    $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'password'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', fn (string $message): bool => str_contains($message, 'seconds') || str_contains($message, 'secunde'));
});

it('signs out only the device that asks', function () {
    $user = User::factory()->create();
    $phone = $user->createToken('phone')->plainTextToken;
    $user->createToken('tablet');

    $this->withToken($phone)->postJson('/api/logout')->assertNoContent();

    expect($user->tokens()->pluck('name')->all())->toBe(['tablet']);
});

it('emails a reset link without telling who has an account', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'ana@example.com']);

    $known = $this->postJson('/api/forgot-password', ['email' => 'ana@example.com'])->assertOk()->json('message');
    $unknown = $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])->assertOk()->json('message');

    expect($known)->toBe($unknown);
    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('answers in the language the app asks for', function () {
    $this->postJson('/api/login', [], ['Accept-Language' => 'ro'])
        ->assertJsonPath('errors.email.0', fn (string $message): bool => str_contains($message, 'obligatoriu'));
});

it('keeps every account endpoint behind a token', function () {
    $this->getJson('/api/me')->assertUnauthorized();
    $this->postJson('/api/posts', ['body' => 'Salut'])->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/me')->assertOk();
});
