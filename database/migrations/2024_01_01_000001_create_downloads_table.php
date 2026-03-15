<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('duration')->nullable();
            $table->string('platform')->nullable();
            $table->string('channel')->nullable();
            $table->string('views')->nullable();
            $table->string('upload_year')->nullable();
            $table->string('format')->default('mp4');
            $table->string('resolution')->default('720');
            $table->bigInteger('filesize')->nullable();
            $table->string('filepath')->nullable();
            $table->string('filename')->nullable();
            $table->enum('status', ['pending','fetching','ready','downloading','done','failed'])
                  ->default('pending');
            $table->integer('progress')->default(0);
            $table->string('speed')->nullable();
            $table->string('eta')->nullable();
            $table->text('error')->nullable();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
