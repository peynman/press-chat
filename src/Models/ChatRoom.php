<?php

namespace Larapress\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Larapress\Profiles\IProfileUser;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property IProfileUser   $author
 * @property int            $id
 * @property int            $flags
 * @property int            $author_id
 * @property array          $data
 * @property IProfileUser[] $participants
 * @property ChatMessage[]  $messages
 * @property ChatMessage    $most_recent
 */
class ChatRoom extends Model
{
    const FLAGS_PUBLIC_JOIN = 1;
    const FLAGS_PUBLIC_WRITE = 2;
    const FLAGS_PUBLIC_INVITE = 4;
    const FLAGS_CLOSED = 8;

    use SoftDeletes;

    protected $table = 'chat_rooms';

    protected $fillable = [
        'author_id',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants() {
        return $this->belongsToMany(
            config('larapress.crud.user.model'),
            'chat_user_pivot',
            'room_id',
            'user_id'
        )
            ->using(ChatUserPivot::class)
            ->withPivot([
                'id',
                'data',
                'flags',
            ])
            ->withTimestamps();
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins() {
        return $this->participants()->wherePivot('flags', '&', ChatUserPivot::FLAGS_ADMIN);
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(){
        return $this->hasMany(config('larapress.chat.routes.chat_messages.model'), 'room_id', 'id');
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unseen_messages() {
        return $this->messages()
            ->leftJoin('chat_user_pivot', function (JoinClause $join) {
                $join->on('chat_user_pivot.room_id', '=', 'chat_messages.room_id')
                    ->where('chat_user_pivot.user_id', Auth::user()->id);
            })
            ->where('chat_messages.created_at', '>', '0' /** DB::raw('`chat_user_pivot`.`updated_at`') */);
    }
}
