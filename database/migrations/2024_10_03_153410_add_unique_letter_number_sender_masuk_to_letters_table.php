<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueLetterNumberSenderMasukToLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->unique(['letter_number', 'sender'], 'unique_letter_number_sender_masuk')->where('type', 'masuk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropUnique('unique_letter_number_sender_masuk');
        });
    }
}
