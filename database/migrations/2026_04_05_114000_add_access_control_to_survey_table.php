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
        Schema::table('survey', function (Blueprint $table) {
            $table->string('access_level')->default('public')->after('mode')->comment('public, auth, role');
            $table->json('allowed_roles')->nullable()->after('access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey', function (Blueprint $table) {
            $table->dropColumn(['access_level', 'allowed_roles']);
        });
    }
};
