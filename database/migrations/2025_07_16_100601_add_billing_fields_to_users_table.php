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
        Schema::table('users', function (Blueprint $table) {
            // Billing Information
            $table->string('billing_company')->nullable();
            $table->string('billing_address_line_1')->nullable();
            $table->string('billing_address_line_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->nullable()->default('United States');
            $table->string('billing_tax_id')->nullable(); // For VAT/Tax ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'billing_company',
                'billing_address_line_1',
                'billing_address_line_2',
                'billing_city',
                'billing_state',
                'billing_postal_code',
                'billing_country',
                'billing_tax_id'
            ]);
        });
    }
};
