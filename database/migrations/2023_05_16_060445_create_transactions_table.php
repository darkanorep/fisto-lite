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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->boolean('is_ap_received')->default(false);
            $table->boolean('is_ap_approved')->default(false);
            $table->enum('status', ['pending', 'received', 'approved', 'reject', 'transmitted'])->default('pending');
            $table->unsignedBigInteger('requestor_id');
            $table->string('state')->nullable();
            $table->string('tag_no')->nullable();
            $table->timestamps();
            $table->foreign('requestor_id')->references('id')->on('users');
            $table->foreign('form_id')->references('id')->on('forms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
