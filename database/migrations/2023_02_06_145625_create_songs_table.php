<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('song_title');
            $table->boolean('solo_song');
            $table->boolean('duo_song');
            $table->boolean('group_song');
            $table->string('media_id')->nullable();
            $table->unsignedBigInteger('band_id');
            $table->unsignedBigInteger('lyric_id');
            $table->unsignedBigInteger('melody_id');
            $table->unsignedBigInteger('album_id');
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('recording_id')->nullable();
            $table->unsignedBigInteger('producer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('songs');
    }
};
