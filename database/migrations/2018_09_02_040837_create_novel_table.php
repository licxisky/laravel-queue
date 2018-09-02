<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNovelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('novels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->string('host');
            $table->string('type');
            $table->string('image');
            $table->string('category');
            $table->string('author');
            $table->string('book_name');
            $table->string('read_url');
            $table->string('url');
            $table->string('status');
            $table->date('update_time');
            $table->string('latest_chapter_name');
            $table->string('latest_chapter_url');
            $table->integer('chapter_count');
            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
            $table->index(['host', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('novels');
    }
}
