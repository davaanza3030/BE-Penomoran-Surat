<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 50)->unique();
            $table->date('date');
            $table->string('recipient');
            $table->unsignedBigInteger('category_id');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('attachments')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_letters');
    }
}
