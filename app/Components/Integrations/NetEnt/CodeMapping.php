<?php

namespace App\Components\Integrations\NetEnt;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    const SUCCESS = 'success';
    const HMAC = 'hmac';
    const TIME = 'time';
    const TOKEN = 'token';

    public static function getMapping(){
        return [
            StatusCode::OK => [
                'message'   => '',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::HMAC => [
                'message'   => 'wrong hmac parameter',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::HMAC]
            ],
            StatusCode::CURRENCY => [
                'message'   => 'Currency mismatch with user wallet currency',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TIME]
            ],
            StatusCode::TOKEN=> [
                'message'   => 'invalid token',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            StatusCode::INSUFFICIENT_FUNDS=> [
                'message' => 'insufficient funds',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            StatusCode::BAD_OPERATION_ORDER => [
                'message' => 'there is no DEBIT with provided i_gameid',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::TOKEN]
            ],

            StatusCode::UNKNOWN => [
                'message' => '408 Request Timeout',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ],

            StatusCode::DUPLICATED_WIN => [
                'message'   => 'Duplicated CREDIT operation',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],

            StatusCode::METHOD => [
                'message'   => 'wrong `type` parameter',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ],
        ];
    }

    public static function isAttribute($key):bool
    {
        return in_array($key, [
            'type', 'tid', 'userid', 'currency', 'amount', 'hmac', 'i_gameid',
            'i_extparam', 'i_gamedesc', 'i_actionid', 'i_rollback'
        ]);
    }

    public static function isTransactionAttribute($key):bool
    {
        return in_array($key, [
            'tid', 'userid', 'currency', 'amount'
        ]);
    }
}