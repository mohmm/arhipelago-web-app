<?php

use Database\Seeders\DemoCommunitySeeder;

/*
 * database/seeders/data/community.json: the demonstration travellers and what
 * they wrote. DemoCommunitySeeder quietly skips a post about a place that is
 * not in the catalogue, and a second review by the same traveller replaces the
 * first, so a typo would lose content without any error.
 */

/**
 * @return array<string, mixed>
 */
function communityData(): array
{
    return json_decode((string) file_get_contents(DemoCommunitySeeder::DATA), true, flags: JSON_THROW_ON_ERROR);
}

it('has everything written by a known traveller, after they joined', function () {
    $data = communityData();
    $joined = array_column($data['users'], 'joined_days_ago', 'key');
    $keys = array_column($data['posts'], 'key');
    $posts = array_column($data['posts'], null, 'key');

    expect($keys)->toBe(array_values(array_unique($keys)));

    foreach ([...$data['posts'], ...$data['reviews']] as $item) {
        expect($joined)->toHaveKey($item['author'])
            ->and($item['days_ago'])->toBeLessThan($joined[$item['author']], "{$item['author']} wrote before joining");
    }

    foreach ($data['comments'] as $comment) {
        expect($posts)->toHaveKey($comment['post'])
            ->and($joined)->toHaveKey($comment['author'])
            ->and($posts[$comment['post']]['days_ago'])->toBeLessThan($joined[$comment['author']], "{$comment['author']} commented before joining");
    }
});

it('writes only about places and routes in the catalogue', function () {
    $content = json_decode((string) file_get_contents(database_path('seeders/data/content.json')), true, flags: JSON_THROW_ON_ERROR);
    $places = array_column($content['points_of_interest'], 'slug');
    $experiences = array_map(fn (array $experience): string => "{$experience['destination']}/{$experience['kind']}", $content['experiences']);
    $data = communityData();

    foreach ([...$data['posts'], ...$data['reviews']] as $item) {
        isset($item['place'])
            ? expect($places)->toContain($item['place'])
            : expect($experiences)->toContain($item['experience']);
    }
});

it('gives each traveller one review per place or route, rated 1 to 5', function () {
    $reviews = communityData()['reviews'];
    $subjects = array_map(fn (array $review): string => $review['author'].' on '.($review['place'] ?? $review['experience']), $reviews);

    expect($subjects)->toBe(array_values(array_unique($subjects)));

    foreach ($reviews as $review) {
        expect($review['rating'])->toBeBetween(1, 5);
    }
});

it('keeps every post photo beside the data', function () {
    foreach (communityData()['posts'] as $post) {
        if (isset($post['photo'])) {
            expect(DemoCommunitySeeder::PHOTOS.'/'.$post['photo'])->toBeFile();
        }
    }
});
