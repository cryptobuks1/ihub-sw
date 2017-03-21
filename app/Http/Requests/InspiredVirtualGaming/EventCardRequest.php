<?php

namespace App\Http\Requests\InspiredVirtualGaming;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class EventCardRequest extends BaseInspiredRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'events' => 'bail|required',
            'events.NumEvents' => 'bail|required|min:0',
            'events.event' => 'bail|required|array',
        ];
    }
}