<?php

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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('reactionable'); // reactionable_id, reactionable_type
            $table->enum('reaction_type', ['like', 'dislike']);
            $table->timestamps();

            // Ensure a user can only react once to a specific item
            $table->unique(['user_id', 'reactionable_id', 'reactionable_type'], 'user_reaction_unique');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
