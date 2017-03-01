<?php

namespace App\Http\Requests\NetEntertainment;

/**
 * Class WinRequest
 * @package App\Http\Requests\NetEntertainment
 */
class WinRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'tid' => 'bail|required|min:1',
            'userid' => 'bail|required|integer|min:1',
            'currency' => 'bail|required|string|min:2',
            'amount' => 'bail|required|min:1',
        ]);
    }
}