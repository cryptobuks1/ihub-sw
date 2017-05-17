<?php

namespace App\Components\Integrations\WirexGaming;

use App\Components\Transactions\Strategies\MicroGaming\ProcessWirexGaming;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

/**
 * Class WirexGamingHelper
 * @package App\Components\Integrations\WirexGaming
 */
class WirexGamingHelper
{
    /**
     * @param $serverPid
     * @return mixed
     */
    protected static function getConfigPid($serverPid)
    {
        $partnersConfig = config('integrations.wirexGaming.partners_config');
        if ($partnersConfig) {
            $partnersConfig = collect($partnersConfig);
            $partnerConfig = $partnersConfig->where('server_pid', '=', $serverPid)->first();
            return $partnerConfig['previous_context_id'];
        }
        return config('integrations.wirexGaming.previous_context_id');
    }

    /**
     * @param $serverPid
     * @param $uid
     * @return int
     */
    public static function parseUid($serverPid, $uid)
    {
        $previousContextId = self::getConfigPid($serverPid);
        if (empty($previousContextId)) {
            throw new ApiHttpException(
                409,
                'Config error',
                CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
            );
        }
        return ($uid - $previousContextId) >> 16;
    }

    /**
     * @param $userCurrency
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    public static function checkSessionCurrency($userCurrency)
    {
        if ($userCurrency != \app('GameSession')->get('currency')) {
            throw new ApiHttpException(
                409,
                'Currency mismatch',
                CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY)
            );
        }
    }

    /**
     * @param TransactionRequest $transactionRequest
     * @param $user
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    public static function handleTransaction($transactionRequest, $user)
    {
        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessWirexGaming());

        if ($transactionResponse->isDuplicate()) {
            throw new ApiHttpException(
                409,
                null,
                \array_merge(CodeMapping::getByMeaning(CodeMapping::DUPLICATE))
            );
        }
        if ($transactionResponse->operation_id === null) {
            throw new ApiHttpException(
                504,
                null,
                CodeMapping::getByMeaning(CodeMapping::TIMED_OUT)
            );
        }
        return $transactionResponse;
    }
}