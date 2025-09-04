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
        Schema::create('file_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('transfer_id')->index();
            $table->string('source_disk');
            $table->string('source_filepath');
            $table->string('destination_disk');
            $table->string('destination_filepath');
            $table->timestamp('time_started')->nullable();
            $table->timestamp('time_finished')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_transfer_logs');
    }
};
