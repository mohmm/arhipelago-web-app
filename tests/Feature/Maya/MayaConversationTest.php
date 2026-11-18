<?php

use App\Ai\Agents\Maya;
use App\Enums\ExperienceKind;
use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\MayaMessage;
use App\Models\PointOfInterest;
use App\Models\User;
use App\Settings\MayaSettings;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => $this->withHeader('Accept-Language', 'ro'));

/**
 * One structured reply, shaped the way Maya's schema asks for it.
 *
 * @param  list<int>  $places
 * @param  list<int>  $experiences
 * @param  list<string>  $suggestions
 * @return array<string, mixed>
 */
function mayaSays(string $reply, array $places = [], array $experiences = [], array $suggestions = []): array
{
    return ['reply' => $reply, 'point_of_interest_ids' => $places, 'experience_ids' => $experiences, 'suggestions' => $suggestions];
}

it('starts a conversation about a place and answers its first message with cards', function () {
    $bran = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    $village = PointOfInterest::factory()->create(['name_ro' => 'Muzeul Satului Brănean']);
    $walk = Experience::factory()->kind(ExperienceKind::CarFree)->through([$bran, $village])->create();
    Maya::fake([mayaSays('Da, sunt la 303 m unul de altul.', [$village->id], [$walk->id], ['Ce mai e în jur?'])]);
    Sanctum::actingAs(User::factory()->create(['first_name' => 'Ana', 'travel_preferences' => 'Trasee scurte.']));

    $this->postJson('/api/maya/conversations', [
        'point_of_interest_id' => $bran->id,
        'message' => 'Pot ajunge pe jos de la Muzeul Satului Brănean?',
    ])
        ->assertCreated()
        ->assertJsonPath('data.subject.type', 'point_of_interest')
        ->assertJsonPath('data.subject.point_of_interest.name', 'Castelul Bran')
        ->assertJsonPath('data.messages.0.role', 'user')
        ->assertJsonPath('data.messages.1.role', 'assistant')
        ->assertJsonPath('data.messages.1.content', 'Da, sunt la 303 m unul de altul.')
        ->assertJsonPath('data.messages.1.points_of_interest.0.name', 'Muzeul Satului Brănean')
        ->assertJsonPath('data.messages.1.experiences.0.kind', 'car_free')
        ->assertJsonPath('data.messages.1.suggestions', ['Ce mai e în jur?']);

    Maya::assertPrompted(function ($prompt): bool {
        $instructions = $prompt->agent->instructions();

        return $prompt->prompt == 'Pot ajunge pe jos de la Muzeul Satului Brănean?'
            && str_contains($instructions, app(MayaSettings::class)->instructions)
            && str_contains($instructions, 'Reply in Romanian')
            && str_contains($instructions, 'Trasee scurte.')
            && str_contains($instructions, 'Castelul Bran');
    });
});

it('reads the earlier messages before answering the next one', function () {
    $traveller = User::factory()->create();
    $conversation = MayaConversation::factory()->for($traveller)->create();
    $conversation->messages()->create(['role' => MayaMessage::ROLE_USER, 'content' => 'Vreau castele.']);
    $conversation->messages()->create(['role' => MayaMessage::ROLE_ASSISTANT, 'content' => 'Îți recomand Branul.']);
    Maya::fake([mayaSays('Da, și pentru copii.')]);
    Sanctum::actingAs($traveller);

    $this->postJson("/api/maya/conversations/{$conversation->id}/messages", ['content' => 'Și pentru copii?'])
        ->assertCreated()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.1.content', 'Da, și pentru copii.');

    Maya::assertPrompted(fn ($prompt): bool => collect($prompt->agent->messages())->pluck('content')->all() == ['Vreau castele.', 'Îți recomand Branul.']);

    expect($conversation->messages()->count())->toBe(4);
});

it('never turns an id that is not a published place or experience into a card', function () {
    $published = PointOfInterest::factory()->create();
    $draft = PointOfInterest::factory()->unpublished()->create();
    Maya::fake([mayaSays('Iată.', [$draft->id, 999999, $published->id], [123456])]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/maya/conversations', ['message' => 'Ce să văd?'])
        ->assertCreated()
        ->assertJsonCount(1, 'data.messages.1.points_of_interest')
        ->assertJsonCount(0, 'data.messages.1.experiences');

    expect(MayaMessage::query()->where('role', 'assistant')->first()->attachments)
        ->toBe([['type' => 'point_of_interest', 'id' => $published->id]]);
});

it('stores nothing and asks the traveller to try again when the model fails', function () {
    $traveller = User::factory()->create();
    $conversation = MayaConversation::factory()->for($traveller)->create();
    Maya::fake([fn () => throw new RuntimeException('The model timed out.')]);
    Sanctum::actingAs($traveller);

    $this->postJson("/api/maya/conversations/{$conversation->id}/messages", ['content' => 'Salut'])
        ->assertStatus(503)
        ->assertJsonPath('message', 'Maya nu poate răspunde acum. Încearcă din nou peste câteva momente.');

    expect(MayaMessage::query()->count())->toBe(0);
});

it('keeps each conversation private to its traveller', function () {
    $conversation = MayaConversation::factory()->create();
    Maya::fake();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/maya/conversations/{$conversation->id}")->assertNotFound();
    $this->postJson("/api/maya/conversations/{$conversation->id}/messages", ['content' => 'Salut'])->assertNotFound();
    $this->deleteJson("/api/maya/conversations/{$conversation->id}")->assertNotFound();

    Maya::assertNeverPrompted();
});

it('lists the traveller\'s conversations, latest first', function () {
    $traveller = User::factory()->create();
    MayaConversation::factory()->for($traveller)->create(['title' => 'Mai veche', 'last_message_at' => now()->subDay()]);
    MayaConversation::factory()->for($traveller)->create(['title' => 'Mai nouă', 'last_message_at' => now()]);
    MayaConversation::factory()->create(['title' => 'A altcuiva']);
    Sanctum::actingAs($traveller);

    $this->getJson('/api/maya/conversations')->assertJsonPath('data.*.title', ['Mai nouă', 'Mai veche']);
});

it('deletes a conversation with its messages', function () {
    $traveller = User::factory()->create();
    $conversation = MayaConversation::factory()->for($traveller)->create();
    $conversation->messages()->create(['role' => MayaMessage::ROLE_USER, 'content' => 'Salut']);
    Sanctum::actingAs($traveller);

    $this->deleteJson("/api/maya/conversations/{$conversation->id}")->assertNoContent();

    expect(MayaMessage::query()->count())->toBe(0);
});

it('refuses a message longer than the limit', function () {
    $traveller = User::factory()->create();
    $conversation = MayaConversation::factory()->for($traveller)->create();
    Sanctum::actingAs($traveller);

    $this->postJson("/api/maya/conversations/{$conversation->id}/messages", ['content' => str_repeat('a', config('maya.max_message_length') + 1)])
        ->assertJsonValidationErrors('content');
});

it('deletes a conversation a year after its last message, with its messages', function () {
    $old = MayaConversation::factory()->create(['last_message_at' => now()->subDays(366)]);
    $recent = MayaConversation::factory()->create(['last_message_at' => now()->subDays(364)]);
    $old->messages()->create(['role' => MayaMessage::ROLE_USER, 'content' => 'Ce văd la Bran?']);

    $this->artisan('model:prune', ['--model' => MayaConversation::class])->assertSuccessful();

    expect(MayaConversation::pluck('id')->all())->toBe([$recent->id])
        ->and(MayaMessage::count())->toBe(0);
});
