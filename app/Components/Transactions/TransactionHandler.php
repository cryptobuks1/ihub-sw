<?php

namespace App\Components\Transactions;


use App\Components\ExternalServices\AccountManager;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Facades\AppLog;
use App\Models\ObjectIdMap;

/**
 * @property  TransactionRequest $request
 * @property  IntegrationUser $user
 */
class TransactionHandler
{
    private $request;
    private $user;

    /**
     * TransactionHandler constructor.
     * @param TransactionRequest $request
     * @param IntegrationUser $user
     */
    public function __construct($request, $user)
    {
        $request->object_id = ObjectIdMap::getObjectId($request->object_id, $request->service_id);

        $this->request = $request;
        $this->user    = $user;
    }

    /**
     * @param TransactionProcessorInterface $strategy
     * @return TransactionResponse
     */
    public function handle(TransactionProcessorInterface $strategy)
    {
        $strategy->process($this->request);

        $transactionData = $strategy->getTransactionData();
        $isDuplicate = $strategy->isDuplicate();

        $balance = $this->user->getBalance();

        $response = $this->buildResponse($transactionData, $isDuplicate, $balance);

        AppLog::info([
            'request' => $this->request->getAttributes(),
            'response' => $response->getAttributes()
        ], '', 'transaction');

        return $response;
    }

    private function buildResponse(array $transactionData, bool $isDuplicate, float $balance){
        return new TransactionResponse($transactionData, $isDuplicate, $balance);
    }
}