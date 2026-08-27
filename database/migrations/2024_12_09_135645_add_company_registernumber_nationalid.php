<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     public function up()
     {
         Schema::table('company_addresses', function (Blueprint $table) {
             $table->string('company_nid')->nullable();
             $table->string('company_rn')->nullable();
         });
     }
 
     public function down()
     {
         Schema::table('company_addresses', function (Blueprint $table) {
             $table->dropColumn(['company_nid', 'company_rn']);
         });
     }
};