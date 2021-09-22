<?php

namespace Larapress\Chat\Services\Chat;

use Larapress\Profiles\IProfileUser;
use Larapress\CRUD\Services\Pagination\PaginatedResponse;

interface IChatRepository {
    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param int $page
     * @param int|null $limit
     *
     * @return PaginatedResponse
     */
    public function getJoinedRoomsPaginated (IProfileUser $user, $page = 0, $limit = null);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param int $page
     * @param int|null $limit
     *
     * @return PaginatedResponse
     */
    public function getClosedRoomsPaginated (IProfileUser $user, $page = 0, $limit = null);

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
    public function getRoomMessagesPaginated (IProfileUser $user, $room, $page = 0, $limit = null);
}
