<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('author_id', false, true);
            $table->bigInteger('room_id', false, true);
            $table->bigInteger('parent_id', false, true)->nullable();
            $table->text('message')->nullable();
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
                    'room_id',
                    'parent_id',
                    'flags'
                ],
                'chat_rooms_full_index'
            );

            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('room_id')->references('id')->on('chat_rooms');
            $table->foreign('parent_id')->references('id')->on('chat_messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
