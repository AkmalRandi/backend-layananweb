<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('subject')->nullable();
            $table->text('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->enum('visibility', ['publish', 'private'])->default('private');
            $table->string('join_code', 10)->unique();
            $table->integer('total_time')->default(10);
            $table->integer('total_points')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quizzes');
    }
};