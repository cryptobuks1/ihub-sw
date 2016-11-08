<?php

namespace App\Http\Requests\GameSession;

/**
 * Class SessionStartRequest
 * @package App\Http\Requests\GameSession
 */
class SessionStartRequest extends BaseGameSessionRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'bail|required|numeric',
            'project_id' => 'bail|required|string',
            'game_id' => 'bail|required',
            'currency' => 'bail|required|string',
        ];
    }
}
