<?php

namespace Larapress\Chat\Services\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 */
class RoomCreateRequest extends FormRequest
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
            'participants.*' => 'nullable|exists:users,id',
            'data' => 'nullable|json_object',
            'flags' => 'nullable|numeric',
            'message.data' => 'nullable|json_object',
            'message.flags' => 'nullable|numeric',
            'message.message' => 'nullable|string',
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getParticipants()
    {
        return $this->get('participants', []);
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
     * @return boolean
     */
    public function hasMessage()
    {
        return !is_null($this->getMessageData()) || !is_null($this->getMessageString());
    }

    /**
     * Undocumented function
     *
     * @return string|null
     */
    public function getMessageString()
    {
        return $this->input('message.message');
    }

    /**
     * Undocumented function
     *
     * @return array|null
     */
    public function getMessageData()
    {
        return $this->input('message.data');
    }

    /**
     * Undocumented function
     *
     * @return int|null
     */
    public function getMessageFlags()
    {
        return $this->input('message.flags', 0);
    }
}
