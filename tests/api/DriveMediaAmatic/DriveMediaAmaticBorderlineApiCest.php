<?php

use iHubGrid\Accounting\ExternalServices\AccountManager;
use DriveMedia\TestUser;
use Testing\DriveMediaAmatic\AccountManagerMock;
use Testing\DriveMediaAmatic\Params;

class DriveMediaAmaticBorderlineApiCest
{
    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    /** @var  Params */
    private $params;

    public function _before()
    {
        $this->key = config('integrations.DriveMediaAmatic.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAmatic.spaces.FUN.id');

        $this->params = new Params();
        $this->testUser = new TestUser();
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

        $request = [
            'space'     => $this->space,
            'login'     => $this->params->login,
            'cmd'       => 'writeBet',
            'bet'       => '0.0',
            'winLose'   => '0.1',
            'tradeId'   => (string)microtime().rand(0,9),
            'betInfo'   => 'bet',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '[6,5,3,6,1,8,7,5,4]',
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }


    public function testMethodBetWin(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->bet()->win()->get());

        $request = [
            'space'     => $this->space,
            'login'     => $this->params->login,
            'cmd'       => 'writeBet',
            'bet'       => $this->params->amount,
            'winLose'   => $this->params->amount,
            'tradeId'   => $this->params->getTradeId(),
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => '[6,5,3,6,1,8,7,5,4]',
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->params->balance)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $request = [
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
            'cmd'   => 'getBalance',
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5(http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

    public function testMethodSpaceNotFound(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1',
            'login' => $this->testUser->getUserId(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

    private function mockAccountManager(\ApiTester $I, $mock)
    {
        if($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }
    }
}