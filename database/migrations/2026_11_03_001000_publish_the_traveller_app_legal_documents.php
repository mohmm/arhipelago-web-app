<?php

use Database\Seeders\LegalDocumentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces the business service's legal documents (version 1.x) with the
 * traveller app's (version 2.0).
 *
 * Saved through the model, so the activity log keeps the wording each one
 * replaces. A document already at 2.0 or later is left alone, so running this
 * on a fresh database, where the seeder has just written 2.0, changes nothing.
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
