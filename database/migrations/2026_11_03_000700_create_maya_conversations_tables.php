<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conversations between a traveller and Maya.
 *
 * A conversation may be about one point of interest or experience ("Conversație
 * despre Castelul Bran"). Maya's replies can carry cards (the places and routes
 * she recommends) and suggested follow-up questions; both are stored as she
 * gave them, and the cards are resolved to current data when shown.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maya_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('subject');
            $table->string('title')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_message_at']);
        });

        Schema::create('maya_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maya_conversation_id')->constrained()->cascadeOnDelete();
            // user | assistant
            $table->string('role', 10);
            $table->text('content');
            // [{"type": "point_of_interest" | "experience", "id": 1}]
            $table->json('attachments')->nullable();
            // ["Arată-le pe hartă", "Ce vedem pe jos?"]
            $table->json('suggestions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maya_messages');
        Schema::dropIfExists('maya_conversations');
    }
};
