<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 50);
            $table->string('subject', 255);
            $table->date('date');
            $table->enum('type', ['masuk', 'keluar']);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['letter_number', 'type']); // Membuat kombinasi unik nomor surat dan tipe surat
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letters');
    }
}
