<?php

namespace App\Services\Maya;

use App\Ai\Facts;
use App\Models\Experience;
use App\Models\MayaConversation;
use App\Models\PointOfInterest;
use App\Models\User;
use App\Settings\MayaSettings;

/**
 * Everything Maya is told before she answers.
 *
 * The admin panel's text sets who she is and how she speaks. The rules below
 * it are the app's and are not editable: they keep every fact tied to the
 * app's data and her reply in the shape the app can show.
 */
class MayaInstructions
{
    public function __construct(private MayaSettings $settings) {}

    public function for(MayaConversation $conversation, User $traveller, string $locale): string
    {
        $language = config("translatable.languages.{$locale}.name", 'Romanian');

        $sections = [
            trim($this->settings->instructions),
            $this->rules($language),
            "## The traveller\n"
                ."- First name: {$traveller->first_name}\n"
                .'- What they told you about their tastes: '.($traveller->travel_preferences ?: 'nothing yet'),
        ];

        $subject = $this->subject($conversation);

        if ($subject != null) {
            $sections[] = $subject;
        }

        return implode("\n\n", $sections);
    }

    private function rules(string $language): string
    {
        $today = now()->timezone('Europe/Bucharest')->format('l, j F Y');

        return implode("\n", [
            '## Rules of the app (always follow them)',
            "- Reply in {$language}, the language the app is set to, unless the traveller clearly writes in another language.",
            '- Look places and experiences up with your tools before recommending or describing them. Every fact you give about a place, an experience, a distance or a travel time must come from a tool result or from the details in these instructions. When they do not contain the answer, say plainly that you do not know, and suggest checking the official website when there is one.',
            '- Use the names exactly as the tools return them.',
            '- Keep the reply under 90 words. You may put the name of a place or an experience in bold with **double asterisks**. Use no other formatting: no lists, no headings, no links.',
            '- point_of_interest_ids and experience_ids: the ids, as returned by the tools, of the places and experiences the reply recommends or talks about, in the order you mention them, at most 3 places and 2 experiences. Leave them empty when the reply names none.',
            "- suggestions: two short follow-up questions the traveller is likely to ask next, written from the traveller's side, in {$language}, each under six words.",
            "- Today is {$today}, in Romania.",
        ]);
    }

    private function subject(MayaConversation $conversation): ?string
    {
        $subject = $conversation->subject;

        if ($subject instanceof PointOfInterest) {
            $subject->loadMissing(['category', 'experiences' => fn ($experiences) => $experiences->published()->with('destination')]);

            return "## This conversation is about this point of interest\n"
                .'Answer about it first. Its details: '
                .json_encode(Facts::pointOfInterest($subject, detailed: true), JSON_UNESCAPED_UNICODE);
        }

        if ($subject instanceof Experience) {
            $subject->loadMissing(['stops.pointOfInterest', 'destination.experiences' => fn ($siblings) => $siblings->published()->with('destination')]);

            return "## This conversation is about this experience\n"
                .'Answer about it first. Its details: '
                .json_encode(Facts::experience($subject, detailed: true), JSON_UNESCAPED_UNICODE);
        }

        return null;
    }
}
