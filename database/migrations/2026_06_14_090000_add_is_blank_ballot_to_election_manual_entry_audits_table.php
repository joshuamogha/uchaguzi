<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_manual_entry_audits', function (Blueprint $table) {
            $table->boolean('is_blank_ballot')->default(false)->after('payload');
        });
    }

    public function down(): void
    {
        Schema::table('election_manual_entry_audits', function (Blueprint $table) {
            $table->dropColumn('is_blank_ballot');
        });
    }
};
