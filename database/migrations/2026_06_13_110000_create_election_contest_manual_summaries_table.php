<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_contest_manual_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_contest_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('destroyed_entries')->default(0);
            $table->timestamps();

            $table->unique(
                ['election_id', 'election_contest_id'],
                'contest_manual_summaries_election_contest_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_contest_manual_summaries');
    }
};
