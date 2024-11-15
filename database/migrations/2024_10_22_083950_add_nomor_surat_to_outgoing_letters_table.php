<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomorSuratToOutgoingLettersTable extends Migration
{
    public function up()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable();
        });
    }

    public function down()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropColumn('nomor_surat');
        });
    }
}
