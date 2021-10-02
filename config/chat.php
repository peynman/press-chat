<?php

return [
    // allow users with these roles to create public chats
    'restrict_public_chat_to_roles' => [1],

    // allow users with these roles to publish answer on any room
    'restrict_answering_for_roles' => [1],

    // crud resources in chat package
    'routes' => [
        'chat_rooms' => [
            'name' => 'chat-rooms',
            'model' => \Larapress\Chat\Models\ChatRoom::class,
            'provider' => \Larapress\Chat\CRUD\ChatRoomCRUDProvider::class,
        ],
        'chat_messages' => [
            'name' => 'chat-messages',
            'model' => \Larapress\Chat\Models\ChatMessage::class,
            'provider' => \Larapress\Chat\CRUD\ChatMessageCRUDProvider::class,
        ],
    ],

    'permissions' => [
        \Larapress\Chat\CRUD\ChatMessageCRUDProvider::class,
        \Larapress\Chat\CRUD\ChatRoomCRUDProvider::class,
    ],
];
