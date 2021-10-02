<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('author_id', false, true);
            $table->integer('flags')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                [
                    'deleted_at',
                    'created_at',
                    'updated_at',
                    'author_id',
                    'flags'
                ],
                'chat_rooms_full_index'
            );

            $table->foreign('author_id')->references('id')->on('users');
        });

        Schema::create('chat_user_pivot', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('room_id', false, true);
            $table->unique(['room_id', 'user_id']);
            $table->integer('flags')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index([
                'created_at',
                'updated_at',
                'room_id',
                'user_id',
            ], 'chat_user_pivot_full_index');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('room_id')->references('id')->on('chat_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_user_pivot');
        Schema::dropIfExists('chat_rooms');
    }
}
