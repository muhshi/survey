<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_survey', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('survey')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'survey_id']);
        });

        // Migrate existing data from groups.survey_id to pivot table
        $groups = DB::table('groups')->whereNotNull('survey_id')->get();
        foreach ($groups as $group) {
            DB::table('group_survey')->insert([
                'group_id' => $group->id,
                'survey_id' => $group->survey_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the survey_id column from groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['survey_id']);
            $table->dropColumn('survey_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('survey_id')->nullable()->constrained('survey')->cascadeOnDelete();
        });

        // Migrate data back (take first survey per group)
        $pivots = DB::table('group_survey')->get();
        foreach ($pivots as $pivot) {
            DB::table('groups')
                ->where('id', $pivot->group_id)
                ->whereNull('survey_id')
                ->update(['survey_id' => $pivot->survey_id]);
        }

        Schema::dropIfExists('group_survey');
    }
};
