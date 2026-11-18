<?php

use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(LegalDocumentSeeder::class);

    // The limiter is cache-backed and the cache outlives a rolled-back
    // database, so one test would otherwise start already throttled.
    RateLimiter::clear('auth-register');
    app('cache')->flush();

    app()->setLocale('en');
});

/**
 * A sign-up that would succeed, so what is being measured is the limiter and
 * not a validation failure.
 *
 * @return array<string, string>
 */
function signUp(int $n): array
{
    return [
        'first_name' => 'Ana',
        'last_name' => 'Popescu',
        'email' => "ana{$n}@example.com",
        'password' => 'password',
    ];
}

it('offers no sign-up on the website: travellers sign up in the app', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', signUp(1))->assertNotFound();
});

it('stops one address opening accounts without limit', function () {
    // Every account can spend money at OpenAI through Maya, so an open sign-up
    // route is an open tap on the AI bill, not just a spam problem.
    for ($i = 1; $i <= 10; $i++) {
        $this->postJson('/api/register', signUp(100 + $i));
    }

    $this->postJson('/api/register', signUp(200))->assertStatus(429);
});

it('caps login attempts from one address across every account', function () {
    // Deliberately looser than the five-attempt credential limiter inside
    // LoginRequest, which must stay the thing a person who mistypes their
    // password meets first.
    for ($i = 1; $i <= 20; $i++) {
        $this->post('/login', ['email' => "someone{$i}@example.com", 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => 'someone@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
});

it('caps password reset requests per address', function () {
    for ($i = 1; $i <= 6; $i++) {
        $this->post('/forgot-password', ['email' => "person{$i}@example.com"]);
    }

    $this->post('/forgot-password', ['email' => 'person@example.com'])
        ->assertStatus(429);
});

it('caps reset emails aimed at one mailbox even from different addresses', function () {
    // The per-address cap alone would let a botnet bury one person's inbox in
    // reset links sent from our own domain.
    for ($i = 1; $i <= 3; $i++) {
        $this->post('/forgot-password', ['email' => 'victim@example.com'], ['REMOTE_ADDR' => "10.0.0.{$i}"]);
    }

    $this->post('/forgot-password', ['email' => 'victim@example.com'], ['REMOTE_ADDR' => '10.0.0.9'])
        ->assertStatus(429);
});
