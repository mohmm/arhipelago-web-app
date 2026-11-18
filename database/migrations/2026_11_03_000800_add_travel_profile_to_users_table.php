<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The traveller profile shown in the app: the city on the profile page, and
 * what the traveller tells Maya about their tastes ("Ce știe Maya despre tine"),
 * which she reads before recommending anything. The avatar column holds a
 * path on the public disk, and is named for it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('avatar_url', 'avatar_path');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('city', 120)->nullable()->after('bio');
            $table->text('travel_preferences')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['city', 'travel_preferences']);
            $table->renameColumn('avatar_path', 'avatar_url');
        });
    }
};
