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
    public function up()
    {
        // Шаг 1: добавить поле nullable
        Schema::table('reports', function (Blueprint $table) {
            $table->string('target_type')->nullable();
        });

        DB::table('reports')->get()->each(function ($report) {
            $targetType = match ($report->type) {
                'post' => \App\Models\Post::class,
                'comment' => \App\Models\Comment::class,
                'profile' => \App\Models\User::class, 
                default => null,
            };

            if ($targetType) {
                DB::table('reports')
                ->where('id', $report->id)
                ->update(['target_type' => $targetType]);
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->string('target_type')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polymorphic', function (Blueprint $table) {
            //
        });
    }
};
