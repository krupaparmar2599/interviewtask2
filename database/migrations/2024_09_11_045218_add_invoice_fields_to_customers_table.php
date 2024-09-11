<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('total_items')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->integer('total_discount_amount')->default(0);
            $table->integer('total_bill')->default(0);
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['total_items', 'total_amount', 'total_discount_amount', 'total_bill']);
        });
    }
};
