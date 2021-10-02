<?php

namespace Larapress\Chat\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\Traits\CRUDProviderTrait;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;
use Larapress\Notifications\Models\Notification;

class ChatMessageCRUDProvider implements ICRUDProvider
{
    use CRUDProviderTrait;

    public $name_in_config = 'larapress.chat.routes.chat_messages.name';
    public $model_in_config = 'larapress.chat.routes.chat_messages.model';
    public $compositions_in_config = 'larapress.chat.routes.chat_messages.compositions';

    public $verbs = [
        ICRUDVerb::VIEW,
        ICRUDVerb::SHOW,
        ICRUDVerb::EDIT,
        ICRUDVerb::CREATE,
        ICRUDVerb::DELETE,
    ];
    public $validSortColumns = [
        'id',
        'author_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public $filterFields = [
        'author_id' => 'equals:author_id',
    ];
    public $searchColumns = [
        'message',
    ];

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getValidRelations(): array
    {
        return [
            'author' => config('larapress.crud.user.provider'),
            'room' => config('larapress.chat.routes.chat_rooms.provider'),
            'parent' => config('larapress.chat.routes.chat_messages.provider'),
            'children' => config('larapress.chat.routes.chat_messages.provider'),
        ];
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public function getCreateRules(Request $request): array
    {
        return [
            'room_id' => 'required|exists:chat_rooms,id',
            'flags' => 'nullable|numeric',
            'data' => 'nullable|json_object',
            'message' => 'nullable|string',
            'parent_id' => 'nullable|exists:chat_messages,id',
        ];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public function getUpdateRules(Request $request): array
    {
        return $this->getCreateRules($request);
    }

    /**
     * Undocumented function
     *
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate($args): array
    {
        $args['author_id'] = Auth::user()->id;

        return $args;
    }

    /**
     * Undocumented function
     *
     * @param array $args
     * @return array
     */
    public function onBeforeUpdate($args): array
    {
        return $args;
    }

    /**
     * @param Notification $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            return $object->author_id === $user->id;
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param Builder $query
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder
    {
        /** @var IProfileUser|ICRUDUser $user */
        $user = Auth::user();
        if (! $user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            $query->where('author_id', $user->id);
            $query->orWhereHas('room', function($q) use($user) {
                $q->where('author_id', $user->id);
            });
        }

        return $query;
    }
}
