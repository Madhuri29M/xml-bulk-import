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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending|processing|completed|failed
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('processed')->default(0);
            $table->unsignedBigInteger('succeeded')->default(0);
            $table->unsignedBigInteger('failed')->default(0);
            $table->string('batch_id')->nullable();
            $table->string('error_log_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('import_failures', function (Blueprint $t) {
            $t->id();
            $t->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $t->unsignedBigInteger('row_number');
            $t->json('payload');
            $t->json('errors');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_failures');
        Schema::dropIfExists('imports');
    }
};
