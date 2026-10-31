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
        Schema::create('marriage_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('case_id')->unique()->constrained('cases')->cascadeOnDelete();
            $table->date('marriage_date')->nullable();
            $table->string('marriage_certificate_number')->nullable();
            $table->string('kua_or_civil_registry')->nullable();
            $table->boolean('has_children')->default(false);
            $table->integer('children_count')->default(0);
            $table->date('separation_date')->nullable();
            $table->text('last_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriage_details');
    }
};

