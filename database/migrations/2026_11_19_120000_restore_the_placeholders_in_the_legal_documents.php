<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The legal documents used to be filled in with the site's address and session
 * cookie when they were seeded, so a database seeded on a development computer
 * carried http://localhost:8005 and Smartsense's cookie into every site it was
 * copied to. The placeholders go back in; LegalDocument::markdown() fills them
 * in from this site's own configuration whenever a page is shown.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('legal_documents')->get(['id', 'body_ro', 'body_en']) as $document) {
            $bodies = [];

            foreach (['body_ro', 'body_en'] as $column) {
                $body = (string) $document->{$column};
                $restored = (string) preg_replace('~https?://(?:localhost|127\.0\.0\.1)(?::\d+)?~', '[APP_URL]', $body);
                $restored = (string) preg_replace('~\b(?:localhost|127\.0\.0\.1)\b~', '[APP_HOST]', $restored);
                $restored = str_replace('smartsense_session', '[SESSION_COOKIE]', $restored);

                if ($restored !== $body) {
                    $bodies[$column] = $restored;
                }
            }

            if ($bodies !== []) {
                DB::table('legal_documents')->where('id', $document->id)->update($bodies);
            }
        }
    }
};
