<?php

namespace App\Http\Requests\MrSlotty;

/**
 * Class BetWinRequest
 * @package App\Http\Requests\MrSlotty
 */
class BetWinRequest extends BaseMrSlottyRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'action'                => 'bail|required|string',
            'amount'                => 'bail|required|int',
            'win'                   => 'bail|required|int',
            'player_id'             => 'bail|required|string',
            'bet_transaction_id'    => 'bail|required|string',
            'win_transaction_id'    => 'bail|required|string',
            'currency'              => 'bail|string',
            'type'                  => 'bail|string',
            'game_id'               => 'bail|string',
            'round_id'              => 'bail|required|string',
            'freerounds_id'         => 'bail|string',
            'hash'                  => 'bail|required|check_hash',
            'extra'                 => 'bail|required|string'
        ];
    }
}