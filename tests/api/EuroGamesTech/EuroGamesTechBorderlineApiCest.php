<?php

namespace api\EuroGamesTech;

use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;
use \EuroGamesTech\TestData;
use \EuroGamesTech\TestUser;


class EuroGamesTechBorderlineApiCest
{

    private $options;
    private $data;
    /**
     * @var TestUser
     */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
    }

    public function _before()
    {
        $this->options = config('integrations.egt');
    }

    public function _after()
    {
    }

    // tests
    public function testNoBetWin(\ApiTester $I)
    {
        $request = $this->data->win();

        $I->disableMiddleware();
        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Bet was not placed</ErrorMessage>");

        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(\ApiTester $I)
    {
        $request = $this->data->bet();

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($request['GameNumber'], array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testTransactionDuplicate(\ApiTester $I)
    {
        $request = $this->data->bet();

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($request['GameNumber'], array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(409);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1100</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Transaction duplicate</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }

    public function testZeroWin(\ApiTester $I)
    {
        $request = $this->data->win();
        $request['Amount'] = 0;

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($request['GameNumber'], array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testMultiWin(\ApiTester $I)
    {
        $request = $this->data->betWin();

        $I->disableMiddleware();
        $I->sendPOST('/egt/WithdrawAndDeposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->expect('unchanged balance after operation');
        $expectedBalance = $this->testUser->getBalanceInCents();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of both transactions applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $request = $this->data->win($request['GameNumber']);
        $request['Reason'] = 'JACKPOT_END';

        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $expectedBalance += $this->data->getAmount();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BONUS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}