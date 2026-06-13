<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_contest_manual_summaries', function (Blueprint $table) {
            $table->unsignedInteger('blank_entries')->default(0)->after('destroyed_entries');
        });
    }

    public function down(): void
    {
        Schema::table('election_contest_manual_summaries', function (Blueprint $table) {
            $table->dropColumn('blank_entries');
        });
    }
};
