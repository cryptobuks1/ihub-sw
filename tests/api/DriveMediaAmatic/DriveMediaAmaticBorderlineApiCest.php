<?php

use App\Models\DriveMediaAmaticProdObjectIdMap;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMediaAmatic\Params;

class DriveMediaAmaticBorderlineApiCest
{
    private $key;
    private $space;

    /** @var  Params */
    private $params;

    public function _before()
    {
        $this->key = config('integrations.DriveMediaAmatic.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAmatic.spaces.FUN.id');

        $this->params = new Params();
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

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
        $tradeId = (string)microtime();
        $objectId = DriveMediaAmaticProdObjectIdMap::getObjectId($tradeId);
        $bet = 0.1;
        $winLose = 0.1;

        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))->bet($objectId, $bet)->win($objectId, $winLose, $balance - $bet)->mock($I);

        $request = [
            'space'     => $this->space,
            'login'     => $this->params->login,
            'cmd'       => 'writeBet',
            'bet'       => $bet,
            'winLose'   => $winLose,
            'tradeId'   => $tradeId,
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', ($balance)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $request = [
            'space' => $this->space,
            'login' => $this->params->login,
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
            'login' => $this->params->login,
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
}