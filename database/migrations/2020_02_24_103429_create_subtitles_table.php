<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('torrent_id')->unsigned()->index('fk_subtitles_torrents1_idx');
            $table->integer('user_id')->index('fk_subtitles_users1_idx');
            $table->string('title');
            $table->string('file_name');
            $table->string('ext');
            $table->boolean('anonymous')->default(false);
            $table->unsignedBigInteger('hits');
            $table->unsignedBigInteger('size');
            $table->smallInteger('status')->default(0);
            $table->dateTime('moderated_at')->nullable();
            $table->integer('moderated_by')->nullable()->index('moderated_by');
            $table->foreign('torrent_id', 'fk_subtitles_torrents1')->references('id')->on('torrents')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('user_id', 'fk_users_users1')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
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
        Schema::dropIfExists('subtitles');
    }
}
