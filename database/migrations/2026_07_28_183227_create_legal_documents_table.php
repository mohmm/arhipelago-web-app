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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            // Fixed by the routes and the footer links that point at it, which
            // is why the admin screen never offers it for editing.
            $table->string('slug')->unique();
            $table->string('title_ro');
            $table->string('title_en');
            $table->longText('body_ro');
            $table->longText('body_en');
            // What a customer agreed to, in the words the document itself uses.
            // A free string rather than a number, because a date is often the
            // clearest version a legal document can carry.
            $table->string('version', 50);
            $table->date('effective_date')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
