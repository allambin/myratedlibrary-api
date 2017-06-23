<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JoinLibrariesAndBooksTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        Schema::create('library_books', function (Blueprint $table) {
//            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->integer('book_id')->unsigned();
            $table->timestamps();
            $table->foreign('library_id')->references('id')->on('libraries');
            $table->foreign('book_id')->references('id')->on('books');
            $table->unique(['library_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropForeign(['library_id']);
            $table->dropForeign(['book_id']);
            $table->dropUnique(['library_id', 'book_id']);
        });
        Schema::dropIfExists('library_books');
    }
}
