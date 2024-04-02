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
        Schema::create('photos', function (Blueprint $table) {

            $table->id();
            $table->string('fileName');
            $table->string('mimeType');
            $table->string('extension');
            $table->bigInteger('fileSize');
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'deleted'])->default('active');
            $table->foreignId('user_id')->constrained()->onDelete('no action');
            $table->timestamps();
            $table->string('name')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->string('category')->nullable()->default(null);
            $table->text('keyword')->nullable()->default(null);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
