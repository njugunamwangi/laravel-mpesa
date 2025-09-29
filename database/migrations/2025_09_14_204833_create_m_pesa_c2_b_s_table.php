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
        Schema::create('mpesa_c2bs', function (Blueprint $table) {
            $table->id();
            $table->string('Transaction_type')->nullable();
            $table->string('Transaction_ID')->nullable();
            $table->string('Transaction_Time')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->string('Amount')->nullable();
            $table->string('Business_Shortcode')->nullable();
            $table->string('Account_Number')->nullable();
            $table->string('Invoice_no')->nullable();
            $table->string('Organization_Account_Balance')->nullable();
            $table->string('ThirdParty_Transaction_ID')->nullable();
            $table->string('Phonenumber')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('MiddleName')->nullable();
            $table->string('LastName')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_c2bs');
    }
};
