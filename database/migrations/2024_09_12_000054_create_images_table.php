<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');          
            $table->string('url');
            $table->string('alt');
            $table->boolean('main')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
