<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DrivemediaPlaytechProdObjectIdMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('drivemedia_playtech_prod_object_id_map', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('trade_id', 32);
            $table->index(['trade_id'], 'drivemedia_playtech_prod_object_id_map_trade_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('integration')->drop('drivemedia_playtech_prod_object_id_map');
        Schema::table('drivemedia_playtech_prod_object_id_map', function (Blueprint $table) {
            $table->dropIndex('drivemedia_playtech_prod_object_id_map_trade_id');
        });
        Schema::connection('integration')->dropIfExists('drivemedia_playtech_prod_object_id_map');
    }
}
