<?php

namespace Larapress\Chat\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatUserPivot extends Pivot {

    const FLAGS_ADMIN = 1;

    protected $table = 'chat_user_pivot';

    public $incrementing = true;
    protected $primaryKey = 'id';

    public $fillable = [
        'room_id',
        'user_id',
        'data',
        'flags',
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
