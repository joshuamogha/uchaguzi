<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_manual_tallies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();

            $table->unique(['election_id', 'election_contest_id', 'candidate_id'], 'manual_tallies_unique_candidate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_manual_tallies');
    }
};
