<?php

namespace BetGames;

use App\Components\Users\IntegrationUser;
use App\Components\Integrations\BetGames\Signature;
use Testing\Params;

class TestData
{
    const IS_MOCK = true;
    const AMOUNT = 10;
    /**
     * @var IntegrationUser
     */
    private $userId;
    private $currency;
    private $amount;
    private $amount_backup;
    public $bigAmount;

    public function __construct()
    {
//        $this->amount = self::AMOUNT;
        $this->userId = (int)env('TEST_USER_ID');
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT * 100;
        $this->bigAmount = Params::BIG_AMOUNT * 100;
    }

    public function notFound()
    {
        return $this->basic('not_found');
    }

    public function ping()
    {
        $data = [
            'method' => 'ping',
            'token' => '-',
            'time' => time(),
            'params' => null,
        ];
        $sign = new Signature($data);

        return array_merge($data, ['signature' => $sign->getHash()]);
    }

    public function authFailed()
    {
        $data = [
            'method' => 'ping',
            'token' => 'authorization_must_fails',
            'time' => time(),
            'params' => [],
        ];
        $this->setSignature($data);

        return $data;
    }

    public function account()
    {
        return $this->basic('get_account_details');
    }

    public function refreshToken()
    {
        return $this->basic('refresh_token');
    }

    public function newToken()
    {
        return $this->basic('request_new_token');
    }

    public function getBalance()
    {
        return $this->basic('get_balance');
    }

    public function bet($bet_id = null, $trans_id = null)
    {
        return $this->transaction('transaction_bet_payin', $bet_id, $trans_id);
    }

    public function win($bet_id = null, $trans_id = null)
    {
        return $this->transaction('transaction_bet_payout', $bet_id, $trans_id, $this->userId);
    }

    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function resetAmount()
    {
        return $this->amount = $this->amount_backup;
    }

    public function wrongTime($method, $params = null)
    {
        $token = Token::create($this->userId, $this->currency);
        $data = [
            'method' => $method,
            'token' => $token->get(),
            'time' => 12345,
            'params' => $params,
        ];
        $this->setSignature($data);

        return $data;
    }

    private function basic($method, $params = null)
    {
        $data = [
            'method' => $method,
            'token' => (string)(microtime(true) * 10000),
            'time' => time(),
            'params' => $params,
        ];
        $this->setSignature($data);

        return $data;
    }

    private function transaction($method, $bet_id = null, $trans_id = null, $player_id = null)
    {
        $params = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'bet_id' => $bet_id ?? $this->getObjectId(),
            'transaction_id' => $trans_id ?? $this->getObjectId(), //md5(str_random()),
            'retrying' => 0,
        ];
        if ($player_id) {
            $params['player_id'] = $player_id;
        }
        return $this->basic($method, $params);
    }

    private function setSignature(&$data)
    {
        $sign = new Signature($data);
        $data['signature'] = $sign->getHash();
    }

    private function getObjectId()
    {
        return (self::IS_MOCK) ? Params::OBJECT_ID : time() + mt_rand(1, 10000);
    }
}