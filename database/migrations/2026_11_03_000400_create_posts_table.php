<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Community posts: a traveller's photo and impressions, optionally tied to a
 * point of interest.
 *
 * The old app called these "experiences". That name now belongs to the day
 * routes, so the rows move to a table of their own, keeping their ids so the
 * comments and likes on them stay attached. Reviews never belonged on a post
 * (they rate places and routes), so the few that existed are removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->foreignId('point_of_interest_id')->nullable()->constrained('points_of_interest')->nullOnDelete();
            // `experience_id` is added with the experiences table.
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            // Published straight away; moderators can hide a post from the admin panel.
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->index(['is_approved', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        if (Schema::hasTable('experiences')) {
            // Posts deleted in the old app stay deleted.
            DB::table('posts')->insertUsing(
                ['id', 'user_id', 'body', 'image_path', 'latitude', 'longitude', 'likes_count', 'comments_count', 'is_approved', 'created_at', 'updated_at'],
                fn (Builder $query) => $query->from('experiences')->whereNull('deleted_at')->select([
                    'id', 'user_id', DB::raw("COALESCE(content, '')"), 'image_path', 'latitude', 'longitude',
                    'likes_count', 'comments_count', 'is_approved', 'created_at', 'updated_at',
                ]),
            );

            Schema::drop('experiences');
        }

        DB::table('comments')->where('commentable_type', 'App\\Models\\Experience')->update(['commentable_type' => 'post']);
        DB::table('reactions')->where('reactionable_type', 'App\\Models\\Experience')->update(['reactionable_type' => 'post']);

        // What a traveller deletes is gone, which is what they expect and what
        // the privacy notice promises: likes, comments and reviews no longer
        // keep soft-deleted rows. Moderators hide a post instead of deleting it.
        foreach (['reactions', 'comments', 'reviews'] as $table) {
            DB::table($table)->whereNotNull('deleted_at')->delete();

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropSoftDeletes();
            });
        }

        // Comments and likes on posts that no longer exist.
        DB::table('comments')->where('commentable_type', 'post')->whereNotIn('commentable_id', DB::table('posts')->select('id'))->delete();
        DB::table('reactions')->where('reactionable_type', 'post')->whereNotIn('reactionable_id', DB::table('posts')->select('id'))->delete();
        DB::table('reviews')->where('reviewable_type', 'App\\Models\\Experience')->delete();
        DB::table('media')->where('model_type', 'App\\Models\\Experience')->delete();

        // The old counters drifted; start from the rows themselves.
        DB::table('posts')->update([
            'likes_count' => DB::table('reactions')->selectRaw('count(*)')
                ->whereColumn('reactions.reactionable_id', 'posts.id')
                ->where('reactions.reactionable_type', 'post')
                ->where('reactions.reaction_type', 'like'),
            'comments_count' => DB::table('comments')->selectRaw('count(*)')
                ->whereColumn('comments.commentable_id', 'posts.id')
                ->where('comments.commentable_type', 'post'),
        ]);
    }

    /**
     * Posts are the app's community content; there is no older shape worth
     * restoring them to.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
