<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('church_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->string('member_no')->nullable()->unique();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->enum('status', ['draft', 'active', 'closed', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('election_contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('contest_type', ['position', 'community', 'committee'])->default('position');
            $table->unsignedInteger('min_selections')->default(1);
            $table->unsignedInteger('max_selections')->default(1);
            $table->unsignedInteger('required_selections')->default(1);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['election_id', 'name'], 'el_contests_election_name_uq');
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['election_contest_id', 'member_id'], 'candidates_contest_member_uq');
        });

        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number')->nullable();
            $table->string('token_hash', 64)->nullable();
            $table->string('pin_hash')->nullable();
            $table->boolean('is_eligible')->default(true);
            $table->boolean('has_voted')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('voted_at')->nullable();
            $table->timestamp('token_used_at')->nullable();
            $table->timestamps();
            $table->unique(['election_id', 'member_id'], 'voters_election_member_uq');
            $table->index(['election_id', 'has_voted'], 'voters_election_voted_idx');
        });

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->constrained()->cascadeOnDelete();
            $table->string('otp_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->uuid('ballot_code')->unique();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index('election_id', 'ballots_election_idx');
        });

        Schema::create('ballot_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ballot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(
                ['ballot_id', 'election_contest_id', 'candidate_id'],
                'ballot_selections_unique_vote'
            );
        });

        Schema::create('election_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('voter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_logs');
        Schema::dropIfExists('ballot_selections');
        Schema::dropIfExists('ballots');
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('voters');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('election_contests');
        Schema::dropIfExists('elections');
        Schema::dropIfExists('members');
        Schema::dropIfExists('church_groups');
        Schema::dropIfExists('communities');
    }
};
