<?php

use App\Models\Topic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });

        // The app is bilingual (en/ro); the legacy Arabic column becomes optional.
        Schema::table('topics', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->change();
        });

        Topic::withTrashed()->orderBy('id')->get()->each(function (Topic $topic): void {
            $topic->sort_order = $topic->id;
            $topic->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        // Backfill NULL Arabic names before restoring the NOT NULL constraint.
        // name_en is unique, so it also satisfies the surviving name_ar unique index.
        Topic::withTrashed()->whereNull('name_ar')->get()->each(function (Topic $topic): void {
            $topic->name_ar = $topic->name_en;
            $topic->saveQuietly();
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->string('name_ar')->nullable(false)->change();
        });
    }
};
