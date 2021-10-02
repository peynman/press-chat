<?php

namespace Larapress\Chat\Services\Chat;

use Carbon\Carbon;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\Chat\Models\ChatMessage;
use Larapress\Chat\Models\ChatRoom;
use Larapress\Chat\Models\ChatUserPivot;
use Larapress\CRUD\Extend\Helpers;
use Larapress\Profiles\IProfileUser;

class ChatService implements IChatService
{
    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param integer $flags
     * @param array $participants
     * @param array $data
     *
     * @return ChatRoom
     */
    public function createRoom(IProfileUser $author, int $flags, $data = [], $participants = [])
    {
        if (
            config('larapress.chat.restrict_public_chat_to_roles') &&
            !$author->hasRole(config('larapress.chat.restrict_public_chat_to_roles')) &&
            $flags > 0
        ) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        /** @var ChatRoom */
        $room = ChatRoom::create([
            'author_id' => $author->id,
            'flags' => $flags,
            'data' => $data,
        ]);

        $this->addParticipantToRoom($author, $room, $author, [], ChatUserPivot::FLAGS_ADMIN);
        foreach ($participants as $participant) {
            $this->addParticipantToRoom($author, $room, $participant, [], 0);
        }

        $room->load('participants');

        return $room;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     *
     * @return ChatRoom
     */
    public function closeRoom(IProfileUser $author, ChatRoom $room)
    {
        if (!$this->canUserAdministerRoom($author, $room)) {
            throw new AppException(AppException::class);
        }

        $room->update([
            'flags' => $room->flags | ChatRoom::FLAGS_CLOSED,
        ]);

        return $room;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     * @param int|IProfileUser $user
     * @param array $data
     * @param int   $flags
     *
     * @return ChatRoom
     */
    public function addParticipantToRoom(IProfileUser $author, ChatRoom $room, $user, $data, $flags)
    {
        if (is_object($user)) {
            $user = $user->id;
        }

        if (
            !$this->canUserAdministerRoom($author, $room) && !$this->isRoomPublicJoin($room) && $user !== $author->id
        ) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        if (($flags & ChatUserPivot::FLAGS_ADMIN) !== 0 && !$this->canUserAdministerRoom($author, $room)) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        if (!in_array($user, $this->getRoomParticipantIds($room))) {
            $room->participants()->attach($user, [
                'flags' => $flags,
                'data' => $data,
            ]);
        }

        return $room;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     * @param int|IProfileUser $user
     * @param array $data
     * @param int   $flags
     *
     * @return ChatRoom
     */
    public function updateParticipantInRoom(IProfileUser $author, ChatRoom $room, $user, $data, $flags)
    {
        if (is_object($user)) {
            $user = $user->id;
        }

        if (
            !$this->canUserAdministerRoom($author, $room) && !$this->isRoomPublicJoin($room) && $user !== $author->id
        ) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        if (($flags & ChatUserPivot::FLAGS_ADMIN) !== 0 && !$this->canUserAdministerRoom($author, $room)) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }


        $pivot = ChatUserPivot::query()
            ->where('user_id', $user)
            ->where('room_id', $room)
            ->first();

        if (!is_null($pivot)) {
            $pivot->update([
                'flags' => $flags,
                'data' => array_merge($pivot->data, $data),
            ]);
        }

        return $room;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     * @param int|IProfileUser $user
     *
     * @return ChatRoom
     */
    public function removeParticipantFromRoom(IProfileUser $author, ChatRoom $room, $user)
    {
        if (is_object($user)) {
            $user = $user->id;
        }

        if (!$this->canUserAdministerRoom($author, $room) && $user !== $author) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        ChatUserPivot::query()
            ->where('user_id', $user)
            ->where('room_id', $room->id)
            ->delete();

        return $room;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param ChatRoom|int $room
     *
     * @return boolean
     */
    public function markRoomSeenByUser(IProfileUser $user, $room)
    {
        if (is_object($room)) {
            $room = $room->id;
        }

        /** @var ChatUserPivot */
        $pivot = ChatUserPivot::query()
            ->where('user_id', $user->id)
            ->where('room_id', $room)
            ->first();

        if (!is_null($pivot)) {
            $pivot->touch();
        }
    }

    /**
     * Undocumented function
     *
     * @param int|ChatRoom $room
     * @return array
     */
    public function getRoomParticipantIds($room)
    {
        if (is_object($room)) {
            $room = $room->id;
        }

        return Helpers::getCachedValue(
            'chat_room_participants',
            ['room:' . $room],
            24 * 60 * 60,
            true,
            function () use ($room) {
                return ChatUserPivot::query()
                    ->where('room_id', $room)
                    ->select('user_id')
                    ->get()
                    ->pluck('user_id')
                    ->toArray();
            }
        );
    }

    /**
     * Undocumented function
     *
     * @param ChatRoom $room
     * @param int|IProfileUser $user
     * @param string $message
     * @param array $data
     * @param int $flags
     *
     * @return ChatMessage
     */
    public function postMessage(ChatRoom $room, $user, $message, $data, $flags)
    {
        if (is_numeric($user)) {
            $class = config('larapress.crud.user.model');
            /** @var IProfileUser */
            $user = call_user_func([$class, 'find'], $user);
        }

        if (!$this->canUserPostOnRoom($user, $room)) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        /** @var ChatMessage */
        $msg = ChatMessage::create([
            'author_id' => $user->id,
            'room_id' => $room->id,
            'message' => $message,
            '$data' => $data,
            'flags' => $flags,
        ]);

        $msg->load([
            'author.form_profile_default'
        ]);

        // update room seen timestamp
        ChatUserPivot::query()
            ->where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->update([
                'updated_at' => Carbon::now(),
            ]);

        return $msg;
    }

    /**
     * Undocumented function
     *
     * @param ChatMessage $msg
     * @param int|IProfileUser $user
     * @param string $message
     * @param array $data
     * @param int $flags
     *
     * @return ChatMessage
     */
    public function updateMessage(ChatMessage $msg, $user, $message, $data, $flags)
    {
        if (is_numeric($user)) {
            $class = config('larapress.crud.user.model');
            /** @var IProfileUser */
            $user = call_user_func([$class, 'find'], $user);
        }

        if (!$msg->author_id === $user->id && !$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        $msg->update([
            'message' => $message,
            'data' => $data,
            'flags' => $flags,
        ]);

        return $msg;
    }

    /**
     * Undocumented function
     *
     * @param int|ChatMessage $msg
     * @param int|IProfileUser $user
     *
     * @return boolean
     */
    public function removeMessage($msg, $user)
    {
        if (is_numeric($user)) {
            $class = config('larapress.crud.user.model');
            /** @var IProfileUser */
            $user = call_user_func([$class, 'find'], $user);
        }

        if (is_numeric($msg)) {
            $msg = ChatMessage::find($msg);
        }

        if (is_null($msg)) {
            return false;
        }

        if (!$msg->author_id === $user->id && !$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            throw new AppException(AppException::ERR_ACCESS_DENIED);
        }

        $msg->delete();
        return true;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     *
     * @return boolean
     */
    public function canUserPostOnRoom(IProfileUser $author, ChatRoom $room)
    {
        return ($author->id === $room->author_id || ($room->flags & ChatRoom::FLAGS_PUBLIC_WRITE) !== 0 ||
            $author->hasRole(config('larapress.profiles.security.roles.super_role'))) &&
            ($room->flags & ChatRoom::FLAGS_CLOSED) === 0;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     * @return boolean
     */
    public function canUserAdministerRoom(IProfileUser $author, ChatRoom $room)
    {
        if (
            $author->id === $room->author_id ||
            $author->hasRole(config('larapress.profiles.security.roles.super_role')) ||
            $room->admins->pluck('id')->includes($author->id)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $author
     * @param ChatRoom $room
     * @return boolean
     */
    public function canUserInviteRoom(IProfileUser $author, ChatRoom $room)
    {
        return $this->isRoomPublicInvite($room) ||
            $author->hasRole(config('larapress.profiles.security.roles.super_role'));
    }

    /**
     * Undocumented function
     *
     * @param ChatRoom $room
     * @return boolean
     */
    public function isRoomPublicInvite(ChatRoom $room)
    {
        return ($room->flags & ChatRoom::FLAGS_PUBLIC_JOIN) !== 0;
    }

    /**
     * Undocumented function
     *
     * @param ChatRoom $room
     * @return boolean
     */
    public function isRoomPublicWrite(ChatRoom $room)
    {
        return ($room->flags & ChatRoom::FLAGS_PUBLIC_WRITE) !== 0;
    }

    /**
     * Undocumented function
     *
     * @param ChatRoom $room
     * @return boolean
     */
    public function isRoomPublicJoin(ChatRoom $room)
    {
        return ($room->flags & ChatRoom::FLAGS_PUBLIC_JOIN) !== 0;
    }
}
