<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letters', function (Blueprint $table) {
            // Menambahkan kolom baru dengan tipe varchar
            $table->string('sender')->nullable(); // Pengirim surat (nullable untuk surat keluar)
            $table->string('recipient')->nullable(); // Tujuan surat (nullable untuk surat masuk)
            $table->string('attachments', 255)->nullable(); // Lampiran surat (varchar dengan batasan 255 karakter)
            $table->string('description', 255)->nullable(); // Deskripsi surat (varchar dengan batasan 255 karakter)
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
            // Drop kolom jika rollback
            $table->dropColumn(['sender', 'recipient', 'attachments', 'description']);
        });
    }
}
