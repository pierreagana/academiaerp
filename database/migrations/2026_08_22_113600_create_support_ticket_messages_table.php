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
        // Real threaded replies for support tickets — previously the SuperAdmin
        // "reply" form only flipped the ticket's status and discarded the typed
        // message, so the school never actually received it. The ticket's own
        // original `description` still acts as the first message in the thread
        // (synthesized at display time, not duplicated here).
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type'); // 'school' | 'support'
            $table->string('sender_name')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
