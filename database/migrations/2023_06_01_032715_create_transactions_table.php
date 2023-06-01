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
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('document_id')->constrained('documents');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('document_no');
            $table->date('request_date')->now();
            $table->timestamp('document_date')->nullable();
            $table->string('document_amount');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('remarks')->nullable();
            $table->string('status')->default('Pending');
            $table->string('state')->default('Pending');
            $table->boolean('is_received')->default(false);
            $table->timestamps();
            $table->softDeletes();
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
