<?php

use App\Models\DriveMediaAristocratProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaAristocratBorderlineApiCest
{
    private $key;
    private $space;

    /** @var  Params */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before() {
        $this->key = config('integrations.DriveMediaAristocrat.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAristocrat.spaces.FUN.id');

        $this->params = new Params('DriveMediaAristocrat');
        $this->helper = new Helper($this->params);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $tradeId = (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999);
        $objectId = DriveMediaAristocratProdObjectIdMap::getObjectId($tradeId);
        $bet = 0.05;
        $winLose = -0.03;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet)
            ->win($objectId, $bet + $winLose, $balance + $winLose)
            ->mock($I);

        $request = [
            'cmd' => 'writeBet',
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
            'bet' => (string)$bet,
            'winLose' => (string)$winLose,
            'tradeId' => $tradeId,
            'betInfo' => 'Bet',
            'gameId' => '125',
            'matrix' => 'EAGLE,DINGO,BOAR,BOAR,BOAR,;TEN,JACK,KING,QUEEN,TEN,;DINGO,BOAR,DINGO,DINGO,SCATTER,;',
            'WinLines' => 0,
            'date' => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $balance + $winLose),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $request = [
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
            'cmd'   => 'getBalance',
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5(http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
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
            'login' => $this->helper->getLogin(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }
}