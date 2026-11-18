<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Removes the demonstration posts the old website carried: written by staff
 * about places the app does not list, several sharing one stock photo. The
 * community starts with what travellers publish in the app.
 *
 * Every one of them predates the traveller app, whose schema landed on
 * 3 November 2026, so the cut-off is that date. Their comments and likes are
 * polymorphic rows without a foreign key, so they are deleted explicitly, and
 * a photo file goes only when no remaining post uses it.
 */
return new class extends Migration
{
    private const TRAVELLER_APP_LAUNCH = '2026-11-03 00:00:00';

    public function up(): void
    {
        $posts = DB::table('posts')->where('created_at', '<', self::TRAVELLER_APP_LAUNCH);
        $ids = $posts->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $photos = $posts->whereNotNull('image_path')->pluck('image_path')->unique();

        DB::transaction(function () use ($ids): void {
            DB::table('comments')->where('commentable_type', 'post')->whereIn('commentable_id', $ids)->delete();
            DB::table('reactions')->where('reactionable_type', 'post')->whereIn('reactionable_id', $ids)->delete();
            DB::table('posts')->whereIn('id', $ids)->delete();
        });

        $stillUsed = DB::table('posts')->whereIn('image_path', $photos)->pluck('image_path');

        Storage::disk('public')->delete($photos->diff($stillUsed)->values()->all());
    }

    public function down(): void
    {
        // The demonstration posts are not restored.
    }
};
