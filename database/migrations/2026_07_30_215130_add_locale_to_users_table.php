<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The language to write to a person in.
 *
 * Until now the locale came from the URL prefix, which is fine for a page and
 * useless for an email: a webhook, a queued job and a nightly command have no
 * URL, so everything they sent would go out in the application default however
 * the reader happens to use the product. Null means "never stated", which falls
 * back to that default rather than guessing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale', 5)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('locale');
        });
    }
};
