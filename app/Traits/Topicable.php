<?php

namespace App\Traits;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Topicable
{
    /**
     * Get all of the topics for the model.
     */
    public function topics(): MorphToMany
    {
        return $this->morphToMany(Topic::class, 'topicable');
    }

    /**
     * Sync the given topics to the model.
     *
     * @param array $topicIds
     * @return void
     */
    public function syncTopics(array $topicIds): void
    {
        $this->topics()->sync($topicIds);
    }
} 