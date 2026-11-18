<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proof that a particular person agreed to a particular text at a particular
 * moment.
 *
 * The contract already says acceptance happens when an account is created —
 * but the sign-up screen showed nobody anything and recorded nothing, so there
 * was no way to demonstrate what was agreed. A link in the footer is
 * browse-wrap, which is the weakest evidence there is.
 *
 * A row is written once and never touched again. There is no `updated_at` and
 * no soft delete, because an acceptance that can be edited is not evidence. A
 * correction is a new row, in a new context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_acceptances', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // A LegalDocument slug, or a bare slug for an acknowledgement that
            // is not itself a published document.
            $table->string('slug');

            // What the document called itself at the time. Kept alongside the
            // hash because `version` is free text that an admin can leave
            // untouched while rewriting the body, so on its own it cannot
            // identify what was accepted.
            $table->string('version', 50);

            // SHA-256 of the exact body rendered to this person, in the
            // language they were reading. Two columns hold the text — body_ro
            // and body_en — and hashing the wrong one would prove somebody
            // agreed to a translation they never saw.
            $table->string('body_sha256', 64);

            $table->string('locale', 5)->nullable();

            // Where in the product the act happened: 'registration' today,
            // 'checkout' and 'reacceptance' once those exist.
            $table->string('context', 32);

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // A column default rather than an application timestamp, which is
            // also what stops MariaDB attaching an implicit ON UPDATE
            // CURRENT_TIMESTAMP to the first timestamp column in the table.
            $table->timestamp('accepted_at')->useCurrent();

            $table->index(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acceptances');
    }
};
