<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->boolean('aproved')->default(0);
            $table->string('totale_size')->default(0);
            $table->integer('views')->default(0);
            $table->string('rating')->default(0);
            $table->integer('total_downloads')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vocalist_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('collection_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->boolean('is_deleted')->default(0);
            
            //foreign key refreance
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('vocalist_id')->references('id')->on('vocalists')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
