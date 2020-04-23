<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->renameColumn('anonymous', 'anon');
            $table->renameColumn('ext', 'extension');
            $table->renameColumn('size', 'file_size');
            $table->renameColumn('hits', 'downloads');
            $table->text('note')->nullable();
            $table->integer('language_id')->index();
            $table->boolean('verified')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->renameColumn('anon', 'anonymous');
            $table->renameColumn('extension', 'ext');
            $table->renameColumn('downloads', 'hits');
            $table->renameColumn('file_size', 'size');
            $table->dropColumn('verified');
            $table->dropColumn('note');
            $table->dropColumn('language_id');
        });
    }
}
