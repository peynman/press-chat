<?php

namespace Larapress\Chat\Services\Chat;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Larapress\Profiles\IProfileUser;
use Illuminate\Http\Response;
use Larapress\Chat\Services\Chat\Requests\RoomCreateRequest;
use Larapress\Chat\Services\Chat\Requests\RoomMessageRequest;
use Larapress\Chat\Services\Chat\Requests\RoomParticipantRequest;
use Larapress\Chat\Services\Chat\Requests\RoomUpdateRequest;

class ChatController extends Controller
{

    public static function registerRoutes()
    {
        // rooms
        Route::post('chat/create-room', '\\' . self::class . '@createRoom')
            ->name('chat.any.create-room');
        Route::post('chat/close-room', '\\' . self::class . '@closeRoom')
            ->name('chat.any.close-room');

        // room participants
        Route::post('chat/add-room-participant', '\\' . self::class . '@addRoomParticipant')
            ->name('chat.any.add-room-participant');
        Route::post('chat/remove-room-participant', '\\' . self::class . '@removeRoomParticipant')
            ->name('chat.any.remove-room-participant');
        Route::post('chat/mark-seen', '\\' . self::class . '@markRoomSeen')
            ->name('chat.any.mark-seen');

        // messaging
        Route::post('chat/post-message', '\\' . self::class . '@postMessage')
            ->name('chat.any.post-message');
        Route::post('chat/update-message', '\\' . self::class . '@updateMessage')
            ->name('chat.any.update-message');
        Route::delete('chat/remove-message/{id}', '\\' . self::class . '@removeMessage')
            ->name('chat.any.remove-message');
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomCreateRequest $request
     *
     * @return Response
     */
    public function createRoom(IChatService $service, RoomCreateRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        $room = $service->createRoom(
            $user,
            $request->getFlags(),
            $request->getData(),
            $request->getParticipants()
        );

        if ($request->hasMessage()) {
            $service->postMessage(
                $room,
                $user,
                $request->getMessageString(),
                $request->getMessageData(),
                $request->getMessageFlags()
            );
        }

        return [
            'message' => trans('larapress::chat.room_created'),
            'room' => $room,
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomUpdateRequest $request
     *
     * @return Response
     */
    public function closeRoom(IChatService $service, RoomUpdateRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        $service->closeRoom($user, $request->getRoom());

        return [
            'message' => trans('larapress::chat.room_closed'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomParticipantRequest $request
     *
     * @return Response
     */
    public function addRoomParticipant(IChatService $service, RoomParticipantRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        return [
            'message' => trans('larapress::chat.room_updated'),
            'room' => $service->addParticipantToRoom(
                $user,
                $request->getRoom(),
                $request->getParticipantId(),
                $request->getData(),
                $request->getFlags()
            )
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomParticipantRequest $request
     *
     * @return Response
     */
    public function removeRoomParticipant(IChatService $service, RoomParticipantRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        return [
            'message' => trans('larapress::chat.room_updated'),
            'room' => $service->removeParticipantFromRoom(
                $user,
                $request->getRoom(),
                $request->getParticipantId()
            )
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomUpdateRequest $request
     *
     * @return Response
     */
    public function markRoomSeen(IChatService $service, RoomUpdateRequest $request) {
        /** @var IProfileUser */
        $user = Auth::user();
        $room = $service->markRoomSeenByUser($user, $request->getRoom());
        return [
            'message' => trans('larapress::chat.room_updated'),
            'room' => $room,
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomMessageRequest $request
     *
     * @return Response
     */
    public function postMessage(IChatService $service, RoomMessageRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        return [
            'message' => trans('larapress::chat.message_sent'),
            'msg' => $service->postMessage(
                $request->getRoom(),
                $user,
                $request->getMessageString(),
                $request->getData(),
                $request->getFlags(),
            )
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param RoomMessageRequest $request
     *
     * @return Response
     */
    public function editMessage(IChatService $service, RoomMessageRequest $request)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        return [
            'message' => trans('larapress::chat.room_updated'),
            'msg' => $service->updateMessage(
                $request->getMessage(),
                $user,
                $request->getMessageString(),
                $request->getData(),
                $request->getFlags(),
            )
        ];
    }

    /**
     * Undocumented function
     *
     * @param IChatService $service
     * @param int $id
     *
     * @return Response
     */
    public function deleteMesage(IChatService $service, $id)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        return [
            'message' => trans('larapress::chat.room_updated'),
            'deleted' => $service->removeMessage($id, $user),
        ];
    }
}
