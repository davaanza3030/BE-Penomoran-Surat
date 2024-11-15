<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientAbbreviationToOutgoingLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->string('recipient_abbreviation', 10)->after('recipient')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropColumn('recipient_abbreviation');
        });
    }
}
