<?php

namespace Larapress\Chat\Services\Chat;

use Larapress\Profiles\IProfileUser;
use Larapress\CRUD\Services\Pagination\PaginatedResponse;
use Larapress\Chat\Models\ChatMessage;
use Larapress\Chat\Models\ChatRoom;

class ChatRepository implements IChatRepository
{
    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param int $page
     * @param int|null $limit
     *
     * @return PaginatedResponse
     */
    public function getJoinedRoomsPaginated(IProfileUser $user, $page = 0, $limit = null)
    {
        $limit = PaginatedResponse::safeLimit($limit);
        return new PaginatedResponse(
            ChatRoom::query()
                ->whereHas('participants', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->whereRaw('(flags & '.ChatRoom::FLAGS_CLOSED.') = 0')
                ->paginate($limit, ['*'], 'page', $page)
        );
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param int $page
     * @param int|null $limit
     *
     * @return PaginatedResponse
     */
    public function getClosedRoomsPaginated(IProfileUser $user, $page = 0, $limit = null)
    {
        $limit = PaginatedResponse::safeLimit($limit);
        return new PaginatedResponse(
            ChatRoom::query()
                ->whereHas('participants', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->whereRaw('(flags & '.ChatRoom::FLAGS_CLOSED.') != 0')
                ->paginate($limit, ['*'], 'page', $page)
        );
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param ChatRoom|int $room
     * @param int $page
     * @param int|null $limit
     *
     * @return PaginatedResponse
     */
    public function getRoomMessagesPaginated(IProfileUser $user, $room, $page = 0, $limit = null)
    {
        if (is_object($room)) {
            $room = $room->id;
        }
        $limit = PaginatedResponse::safeLimit($limit);
        return new PaginatedResponse(
            ChatMessage::query()
                ->where('room_id', $room)
                ->whereNull('parent_id')
                ->paginate($limit, ['*'], 'page', $page)
        );
    }
}
