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
        Schema::create('m_pesa_b2_b_s', function (Blueprint $table) {
            $table->id();
            $table->string('result_type')->nullable();
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->string('originator_conversation_id')->nullable();
            $table->string('conversation_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_amount')->nullable();
            $table->string('receiver_party_public_name')->nullable();
            $table->string('transaction_date_time')->nullable();
            $table->string('debit_account_current_balance_minimum')->nullable();
            $table->string('debit_account_current_balance_basic')->nullable();
            $table->string('initiator_account_current_balance_minimum')->nullable();
            $table->string('initiator_account_current_balance_basic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_pesa_b2_b_s');
    }
};
