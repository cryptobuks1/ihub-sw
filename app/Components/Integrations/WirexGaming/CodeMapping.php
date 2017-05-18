<?php

namespace App\Components\Integrations\WirexGaming;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;

/**
 * Class CodeMapping
 * @package App\Components\Integrations\WirexGaming
 */
class CodeMapping extends CodeMappingBase
{
    const INVALID_AUTH = 'invalid_auth';
    const BAD_OPERATION_ORDER = 'bad_operation_order';

    public static function getMapping()
    {
        return [
            StatusCode::SYSTEM_ERROR_CODE => [
                'message'   => 'System error',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [
                    self::SERVER_ERROR,
                    self::INVALID_CURRENCY,
                    self::BAD_OPERATION_ORDER,
                    CodeMapping::DUPLICATE,
                    CodeMapping::TIMED_OUT,
                    CodeMapping::UNKNOWN_METHOD,

                ]
            ],
            StatusCode::USER_NOT_AUTHORIZED_CODE => [
                'message'   => 'User not authorized',
                'map'       => [88618],
                'attribute' => null,
                'meanings'  => [self::INVALID_AUTH]
            ],
            StatusCode::AMOUNT_NOT_AVAILABLE_CODE => [
                'message' => 'Insufficient funds',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::NO_MONEY]
            ],
            StatusCode::WRONG_SERVICE_CALLED_CODE => [
                'message' => 'Invalid Service',
                'map'     => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_SERVICE]
            ],
        ];
    }
}
