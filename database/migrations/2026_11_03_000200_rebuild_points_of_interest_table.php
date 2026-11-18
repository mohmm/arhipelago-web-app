<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Points of interest as the app shows them: curated places (castles,
 * fortresses, museums, nature), written in Romanian, English and Arabic,
 * managed in the admin panel and published when ready.
 *
 * The old table held the website's accommodation registry, so it is replaced
 * rather than altered. Its categories and topics were registry unit types and
 * generic interests; the new ones are seeded.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $locales = ['ro', 'en', 'ar'];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('points_of_interest');
        Schema::enableForeignKeyConstraints();

        // Everything that pointed at the old rows goes with them.
        $oldType = 'App\\Models\\PointOfInterest';
        DB::table('media')->where('model_type', $oldType)->delete();
        DB::table('reactions')->where('reactionable_type', $oldType)->delete();
        DB::table('reviews')->where('reviewable_type', $oldType)->delete();
        DB::table('comments')->where('commentable_type', $oldType)->delete();
        DB::table('topicables')->delete();
        DB::table('topics')->delete();
        DB::table('point_of_interest_categories')->delete();

        Schema::table('point_of_interest_categories', function (Blueprint $table): void {
            $table->string('name_ar')->nullable()->after('name_ro');
        });

        Schema::create('points_of_interest', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('point_of_interest_categories')->restrictOnDelete();
            $table->string('slug')->unique();

            foreach ($this->locales as $locale) {
                // Romanian is required by the forms; the other two fall back to it.
                $table->string("name_{$locale}")->nullable();
                // The short kind of place: "Castel", "Cetate", "Natură".
                $table->string("type_{$locale}", 60)->nullable();
                // Where it is, in a list: "Bran", "lângă Zărnești".
                $table->string("locality_{$locale}", 120)->nullable();
                // Where it is, in full: "comuna Bran, județul Brașov".
                $table->string("address_{$locale}")->nullable();
                $table->text("description_{$locale}")->nullable();
            }

            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->boolean('is_historic_monument')->default(false);
            $table->string('website')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_of_interest');

        Schema::table('point_of_interest_categories', function (Blueprint $table): void {
            $table->dropColumn('name_ar');
        });
    }
};
