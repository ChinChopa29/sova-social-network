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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'archived', 'private'])->default('draft');
            $table->json('tags')->nullable(); 
            $table->string('image')->nullable(); 
            $table->boolean('is_commentable')->default(true); 
            $table->unsignedBigInteger('view_count')->default(0)->index(); 
            $table->unsignedBigInteger('like_count')->default(0)->index(); 
            $table->unsignedBigInteger('dislike_count')->default(0)->index(); 
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
