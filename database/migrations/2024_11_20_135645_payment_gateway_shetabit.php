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
        // zarinpal gateway develop by itnoo 
        // if (!Schema::hasTable('companies') && !Schema::hasTable('organisation_settings')) {
            // zarinpal gateway develop by itnoo
            Schema::table('payment_gateway_credentials', function (Blueprint $table) {
                $table->string('zarinpal_merchant_id')->nullable();
                $table->enum('zarinpal_status', ['active', 'deactive'])->nullable()->default('deactive');
            });  
        // }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};