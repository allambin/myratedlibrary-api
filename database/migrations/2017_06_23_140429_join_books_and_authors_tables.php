<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JoinBooksAndAuthorsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author_books', function (Blueprint $table) {
            $table->integer('author_id')->unsigned();
            $table->integer('book_id')->unsigned();
            $table->timestamps();
            $table->foreign('author_id')->references('id')->on('authors');
            $table->foreign('book_id')->references('id')->on('books');
            $table->unique(['author_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('author_books', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropForeign(['book_id']);
            $table->dropUnique(['author_id', 'book_id']);
        });
        Schema::dropIfExists('author_books');
    }
}
