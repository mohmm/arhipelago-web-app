<?php

namespace App\Services\Legal;

use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Turns a ticked box into evidence.
 *
 * What has to survive years later is not "they agreed" but *which words* they
 * agreed to, so every row carries the version the document called itself and a
 * hash of the exact body that was on screen — `version` alone cannot do it,
 * because an admin can rewrite the text without touching it.
 */
class RecordLegalAcceptance
{
    /**
     * The documents the sign-up checkbox names, in the order it names them.
     *
     * One tick, one row per document: the box is only as good as the links
     * beside it, so nothing is recorded that the person was not shown.
     *
     * @var list<string>
     */
    public const REGISTRATION_DOCUMENTS = [LegalDocument::TERMS, LegalDocument::PRIVACY];

    /**
     * Write the acceptances a sign-up produces.
     *
     * Called inside the transaction that creates the user, so a half-written
     * registration cannot leave evidence for an account that does not exist.
     */
    public function atRegistration(User $user, Request $request): void
    {
        $locale = app()->getLocale();
        $documents = $this->documents(self::REGISTRATION_DOCUMENTS);

        $audit = [
            'user_id' => $user->id,
            'locale' => $locale,
            'context' => LegalAcceptance::CONTEXT_REGISTRATION,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => now(),
        ];

        foreach (self::REGISTRATION_DOCUMENTS as $slug) {
            LegalAcceptance::create($audit + [
                'slug' => $slug,
                'version' => $documents[$slug]->version,
                'body_sha256' => $this->hash($documents[$slug], $locale),
            ]);
        }

    }

    /**
     * SHA-256 of the body in the language the person was actually reading.
     *
     * The text lives in two columns, so hashing the wrong one would record that
     * somebody agreed to a translation they never saw. This hashes what was
     * rendered, which is why it legitimately changes if `app.url` changes — it
     * is evidence of a display, not a checksum over a canonical file.
     */
    private function hash(LegalDocument $document, string $locale): string
    {
        $body = $locale == 'ro' ? $document->body_ro : $document->body_en;

        return hash('sha256', (string) $body);
    }

    /**
     * @param  list<string>  $slugs
     * @return array<string, LegalDocument>
     */
    private function documents(array $slugs): array
    {
        $documents = LegalDocument::query()
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug')
            ->all();

        foreach ($slugs as $slug) {
            if (! isset($documents[$slug])) {
                // Loud rather than silent: a sign-up recorded against a
                // document that does not exist is not evidence of anything,
                // and the page the checkbox links to would be a 404 anyway.
                throw new RuntimeException("Cannot record acceptance: the legal document [{$slug}] is missing. Run the LegalDocumentSeeder.");
            }
        }

        return $documents;
    }
}
