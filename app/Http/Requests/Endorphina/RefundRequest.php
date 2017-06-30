<?php

namespace App\Http\Requests\Endorphina;

class RefundRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'currency' => 'bail|required|string',
            'game' => 'bail|sometimes|string',
            'player' => 'bail|required|string',
            'amount' => 'bail|required|numeric|min:1',
            'date' => 'bail|required|numeric',
            'gameId' => 'bail|required|numeric',
            'id' => 'bail|required|numeric',
        ]);
    }

}
