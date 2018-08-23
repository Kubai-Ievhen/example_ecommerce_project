<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url')->unique();
            $table->string('title')->nullable();
            $table->integer('type');
            $table->string('type_file');
            $table->integer('product_id')->nullable();
            $table->integer('icons_group_id');
            $table->integer('left')->default(600);
            $table->integer('top')->default(300);
            $table->string('colors')->nullable();
            $table->integer('price')->default(0);
            $table->string('colorLinkGroup')->default('Base');
            $table->integer('front_back')->default(0);
            $table->boolean('z_changeable')->default(false);
            $table->boolean('removable')->default(false);
            $table->boolean('draggable')->default(false);
            $table->boolean('rotatable')->default(false);
            $table->boolean('resizable')->default(false);
            $table->double('size')->default('1');
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
        Schema::dropIfExists('product_images');
    }
}
