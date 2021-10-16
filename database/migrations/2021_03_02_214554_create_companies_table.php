<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('details')->nullable();
            $table->string('logo')->nullable();
            $table->string('color')->nullable();
            $table->string('http_host')->nullable();
            $table->string('http_protocol')->nullable();
            $table->string('storage_port')->nullable();
            $table->string('remote_ip')->nullable();
            $table->boolean('aproved')->default(1);
            $table->string('email')->nullable();
            $table->string('email2')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('instegram_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->date('expired_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->boolean('is_deleted')->default(0);
            
            //foreign key refreance
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
        Schema::dropIfExists('companies');
    }
}
