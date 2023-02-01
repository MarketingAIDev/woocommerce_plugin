<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_name', 50);
            $table->integer('operator_limit');
            $table->integer('department_limit');
            $table->integer('canned_message_limit');
            $table->decimal('cost', 10, 2);
            $table->integer('duration');
            $table->string('duration_type', 20);
            $table->tinyInteger('is_featured')->default(0);
            $table->text('others')->nullable();
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
        Schema::dropIfExists('packages');
    }
}
