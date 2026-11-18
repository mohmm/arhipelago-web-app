<?php

use Database\Seeders\LegalDocumentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Publishes version 2.1 of the Terms: section 8 now says that the photos of
 * places need no credit, as the app no longer prints one.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new LegalDocumentSeeder)->publishNewerVersions();
    }

    public function down(): void
    {
        // The earlier wording is kept in the activity log, not restored from here.
    }
};
