<?php

use App\Ai\Tools\ExperienceDetails;
use App\Ai\Tools\FindExperiences;
use App\Ai\Tools\FindPointsOfInterest;
use App\Ai\Tools\PointOfInterestDetails;
use App\Ai\Tools\TravelBetween;
use App\Enums\ExperienceKind;
use App\Models\Experience;
use App\Models\PointOfInterest;
use App\Services\Routing\OsrmClient;
use App\Services\Routing\RoutedPath;
use Laravel\Ai\Tools\Request;

beforeEach(fn () => app()->setLocale('ro'));

function useTool(string $tool, array $arguments = []): array
{
    return json_decode(app($tool)->handle(new Request($arguments)), true);
}

it('finds published places by name, never drafts', function () {
    PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    PointOfInterest::factory()->unpublished()->create(['name_ro' => 'Castelul Ciornă']);

    $found = useTool(FindPointsOfInterest::class, ['text' => 'Castelul']);

    expect($found['points_of_interest'])->toHaveCount(1)
        ->and($found['points_of_interest'][0]['name'])->toBe('Castelul Bran');
});

it('describes one place with the experiences that stop there', function () {
    $bran = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran', 'website' => 'https://bran-castle.com']);
    $experience = Experience::factory()->through([PointOfInterest::factory()->create(), $bran])->create();

    $details = useTool(PointOfInterestDetails::class, ['id' => $bran->id]);

    expect($details['website'])->toBe('https://bran-castle.com')
        ->and($details['experiences_stopping_here'])->toBe([
            ['experience_id' => $experience->id, 'title' => $experience->fullTitle(), 'stop_number' => 2],
        ]);
});

it('says plainly when a place does not exist', function () {
    expect(useTool(PointOfInterestDetails::class, ['id' => 404]))->toHaveKey('error');
});

it('finds experiences by kind and by a place they stop at', function () {
    $bran = PointOfInterest::factory()->create();
    Experience::factory()->kind(ExperienceKind::Family)->through([$bran])->create();
    Experience::factory()->kind(ExperienceKind::Winter)->create();

    expect(useTool(FindExperiences::class, ['kind' => 'family'])['experiences'])->toHaveCount(1)
        ->and(useTool(FindExperiences::class, ['point_of_interest_id' => $bran->id])['experiences'])->toHaveCount(1);
});

it('describes one experience with its stops in order', function () {
    $castle = PointOfInterest::factory()->create(['name_ro' => 'Castelul Bran']);
    $museum = PointOfInterest::factory()->create(['name_ro' => 'Muzeul Satului Brănean']);
    $experience = Experience::factory()->through([$castle, $museum])->create();

    $details = useTool(ExperienceDetails::class, ['id' => $experience->id]);

    expect(array_column($details['stops'], 'name'))->toBe(['Castelul Bran', 'Muzeul Satului Brănean'])
        ->and($details['stops'][1]['km_from_start'])->toEqual(1);
});

it('measures the way between two places on real roads', function () {
    $this->mock(OsrmClient::class)->shouldReceive('route')->once()->andReturn(new RoutedPath(303, 240, '', [], []));
    [$from, $to] = PointOfInterest::factory()->count(2)->create();

    $route = useTool(TravelBetween::class, ['from_point_of_interest_id' => $from->id, 'to_point_of_interest_id' => $to->id, 'mode' => 'foot']);

    expect($route)->toMatchArray(['distance_m' => 303, 'duration_minutes' => 4, 'mode' => 'foot']);
});

it('gives the straight-line distance, labelled, when the router cannot be reached', function () {
    $this->mock(OsrmClient::class)->shouldReceive('route')->andThrow(new RuntimeException('Connection refused'));
    $from = PointOfInterest::factory()->at(45.5152, 25.3672)->create();
    $to = PointOfInterest::factory()->at(45.5179, 25.3677)->create();

    $route = useTool(TravelBetween::class, ['from_point_of_interest_id' => $from->id, 'to_point_of_interest_id' => $to->id, 'mode' => 'foot']);

    expect($route)->not->toHaveKey('duration_minutes')
        ->and($route['straight_line_distance_m'])->toBeBetween(290, 320)
        ->and($route['note'])->toContain('straight line');
});
