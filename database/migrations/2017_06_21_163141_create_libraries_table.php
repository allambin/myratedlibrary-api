<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibrariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        Schema::create('library_books', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->integer('book_id')->unsigned();
            $table->foreign('library_id')->references('id')->on('libraries');
            $table->foreign('book_id')->references('id')->on('books');
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
        });
        Schema::dropIfExists('library_books');
        
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('libraries');
    }
}
