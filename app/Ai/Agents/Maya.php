<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Strict;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

/**
 * Maya, the travel assistant every traveller talks to.
 *
 * She looks places and experiences up with her tools and answers in a fixed
 * shape: the reply, the ids of what she recommends (shown as cards) and a few
 * follow-up questions (shown as chips). MayaReplier builds her and keeps only
 * what she says that the app can stand behind.
 */
#[MaxSteps(8)]
#[Strict]
class Maya implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * @param  iterable<Message>  $history
     * @param  iterable<Tool>  $tools
     */
    public function __construct(
        private string $instructions,
        private iterable $history,
        private iterable $tools,
    ) {}

    public function instructions(): string
    {
        return $this->instructions;
    }

    /**
     * @return iterable<Message>
     */
    public function messages(): iterable
    {
        return $this->history;
    }

    /**
     * @return iterable<Tool>
     */
    public function tools(): iterable
    {
        return $this->tools;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'reply' => $schema->string()
                ->description('The answer to the traveller, in their language.')
                ->required(),
            'point_of_interest_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Ids of the points of interest the reply recommends or talks about, in the order mentioned.')
                ->required(),
            'experience_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Ids of the experiences the reply recommends or talks about, in the order mentioned.')
                ->required(),
            'suggestions' => $schema->array()
                ->items($schema->string())
                ->description('Short follow-up questions the traveller might ask next, in their language.')
                ->required(),
        ];
    }
}
