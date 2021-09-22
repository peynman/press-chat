<?php

namespace Larapress\Chat\Services\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\Chat\Models\ChatRoom;

/**
 */
class RoomUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // already handled in CRUD middleware
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'room_id' => 'required|exists:chat_rooms,id',
            'data' => 'nullable|json_object',
            'flags' => 'nullable|numeric',
        ];
    }

    /**
     * Undocumented function
     *
     * @return ChatRoom
     */
    public function getRoom()
    {
        return ChatRoom::find($this->getRoomId());
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->get('room_id');
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getData()
    {
        return $this->get('data', []);
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->get('flags', 0);
    }
}
