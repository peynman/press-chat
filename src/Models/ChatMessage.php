<?php

namespace Larapress\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Larapress\Profiles\IProfileUser;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property IProfileUser   $author
 * @property int            $id
 * @property int            $author_id
 * @property int            $parent_id
 * @property int            $room_id
 * @property int            $flags
 * @property array          $data
 * @property string         $message
 * @property ChatRoom       $room
 * @property Parent         $parent
 * @property ChatMessage[]  $children
 */
class ChatMessage extends Model
{
    const FLAGS_PUBLIC = 1;
    const FLAGS_PRIVATE = 1;

    use SoftDeletes;

    protected $table = 'chat_messages';

    protected $fillable = [
        'author_id',
        'room_id',
        'parent_id',
        'message',
        'data',
        'flags',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(config('larapress.crud.user.model'), 'author_id');
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room() {
        return $this->belongsTo(config('larapress.chat.routes.chat_rooms.model'), 'room_id');
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent() {
        return $this->belongsTo(config('larapress.chat.routes.chat_messages.model'), 'parent_id');
    }


    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function children() {
        return $this->hasMany(config('larapress.chat.routes.chat_messages.model'), 'parent_id');
    }
}
