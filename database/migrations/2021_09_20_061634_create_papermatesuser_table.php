<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePapermatesuserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('papermatesuser', function (Blueprint $table) {
            $table->id('UserId')->unsigned();
            $table->string('username');
            $table->string('	userEmail');
            $table->string('	userpassword');
            $table->string('UserPhone');
            $table->string('AcademicLevel');
            $table->string('UserImage')->default('/paperMates/content/user/user-default/user.png');
            $table->unsignedTinyInteger('IsUserMale');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('papermatesuser');
    }
}
