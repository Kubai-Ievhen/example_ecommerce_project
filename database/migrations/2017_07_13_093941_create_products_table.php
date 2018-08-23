<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->integer('status_active');
            $table->integer('status_approved')->default(0);
            $table->integer('preview_image_id');
            $table->integer('pricing_to_step')->default(1);
            $table->string('name');
            $table->string('url')->unique();
            $table->string('short_description')->nullable();
            $table->text('product_description')->nullable();
            $table->text('seo_data')->nullable();
            $table->text('description')->nullable();
            $table->double('min_quantity')->default(1);
            $table->double('unit_step')->default(1);
            $table->double('price_one_step')->default(1);
            $table->integer('user_id');
            $table->double('discount')->default(0);
            $table->boolean('featured')->default(0);
            $table->boolean('is_base')->default(0);
            $table->boolean('double')->default(0);
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
        Schema::dropIfExists('products');
    }
}
