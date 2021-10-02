<?php

namespace Larapress\Chat\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\Traits\CRUDProviderTrait;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;
use Larapress\Notifications\Models\Notification;
use Larapress\Notifications\Services\Chat\IChatService;

class ChatRoomCRUDProvider implements ICRUDProvider
{
    use CRUDProviderTrait;

    public $name_in_config = 'larapress.chat.routes.chat_rooms.name';
    public $model_in_config = 'larapress.chat.routes.chat_rooms.model';
    public $compositions_in_config = 'larapress.chat.routes.chat_rooms.compositions';

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
        'title',
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
            'participants' => config('larapress.crud.user.provider'),
            'messages' => config('larapress.chat.routes.chat_messages.provider'),
            'unseen_messages' => config('larapress.chat.routes.chat_messages.provider'),
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
            'flags' => 'nullable|numeric',
            'data' => 'nullable|json_object',
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
        if (is_null($args['flags'])) {
            $args['flags'] = 0;
        }

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
        if (is_null($args['flags'])) {
            $args['flags'] = 0;
        }

        return $args;
    }

    public function onAfterCreate($object, array $input_data): void
    {
        /** @var IChatService */
        // $service = app(IChatService::class);
        // @todo: add participants
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
        }

        return $query;
    }
}
