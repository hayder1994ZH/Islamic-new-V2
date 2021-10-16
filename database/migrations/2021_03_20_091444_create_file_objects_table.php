<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_objects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('buket')->nullable();
            $table->string('key')->nullable();
            $table->string('size')->nullable();
            $table->integer('type')->default(0);
            $table->integer('s3')->default(0);
            $table->integer('type_audio')->default(0);
            $table->unsignedBigInteger('file_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->boolean('is_deleted')->default(0);
            
            //foreign key refreance
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_objects');
    }
}
