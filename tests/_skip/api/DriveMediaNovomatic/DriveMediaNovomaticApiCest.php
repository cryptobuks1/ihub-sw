<?php

use App\Models\DriveMediaNovomaticProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaNovomaticApiCest
{
    const URI = '/novomatic';

    const TEST_SPACE = '1807';

    const TEST_GAME_ID = 132;

    /** @var Params  */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before() {
        $this->params = new Params('DriveMediaNovomatic');
        $this->helper = new Helper($this->params);
    }

    public function testGetBalance(ApiTester $I)
    {
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => $this->helper->getLogin(),
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->helper->getLogin(),
            'balance' => (string)round($balance, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    protected function addSignatureToRequestData(&$requestData)
    {
        $signatureMaker = new \App\Components\Integrations\DriveMediaNovomatic\SignatureMaker();
        $signature = $signatureMaker->make(self::TEST_SPACE, $requestData);
        $requestData = array_merge($requestData, ['sign' => $signature]);
    }

    public function testBet(ApiTester $I)
    {
        $bet = 0.01;
        $winLose = -0.01;
        $tradeId = md5(microtime());
        $objectId = DriveMediaNovomaticProdObjectIdMap::getObjectId($tradeId);
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->helper->getLogin(),
            'bet' => (string)$bet,
            'winLose' => (string)$winLose,
            'tradeId' => $tradeId,
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->helper->getLogin(),
            'balance' => (string)round($balance - $bet, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $bet = 0.01;
        $winLose = 0.01;
        $tradeId = md5(microtime());
        $objectId = DriveMediaNovomaticProdObjectIdMap::getObjectId($tradeId);
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet)
            ->win($objectId, $winLose, $balance - $bet + $winLose)
            ->mock($I);

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->helper->getLogin(),
            'bet' => $bet,
            'winLose' => $winLose,
            'tradeId' => $tradeId,
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
            'matrix' => '[]',
            'WinLines' => 0,
            'date' => time(),
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'login' => $this->helper->getLogin(),
            'balance' => (string)round($balance - $bet + $winLose, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }
}
