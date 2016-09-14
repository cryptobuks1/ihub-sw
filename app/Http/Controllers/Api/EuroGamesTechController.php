<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\EgtXmlApiFormatter;
use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Components\Traits\MetaDataTrait;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\EuroGamesTechTemplate;
use App\Http\Requests\EuroGamesTech\AuthRequest;
use App\Http\Requests\EuroGamesTech\DepositRequest;
use App\Http\Requests\EuroGamesTech\PlayerBalanceRequest;
use App\Http\Requests\EuroGamesTech\WithdrawAndDepositRequest;
use App\Http\Requests\EuroGamesTech\WithdrawRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class EuroGamesTechController
 * @package App\Http\Controllers\Api
 */
class EuroGamesTechController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = EuroGamesTechTemplate::class;

    public function __construct(EgtXmlApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.egt');

        $this->middleware('input.xml');

        Validator::extend('check_defence_code', 'App\Http\Requests\Validation\EuroGamesTechValidation@checkDefenceCode');
        Validator::extend('check_expiration_time', 'App\Http\Requests\Validation\EuroGamesTechValidation@checkExpirationTime');
    }

    public function authenticate(AuthRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        if($user->getActiveWallet()->currency != EgtHelper::getCurrencyFromPortalCode($request->input('PortalCode'))){
            throw new ApiHttpException(409, "Currency mismatch", CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }

        return $this->respondOk(200, null,[
            'Balance' => $user->getBalance() * 100
        ]);
    }

    public function getPlayerBalance(PlayerBalanceRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        if($user->getCurrency() != $request->input('Currency')){
            throw new ApiHttpException(409, "Currency mismatch", CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }

        return $this->respondOk(200, null,[
            'Balance' => $user->getBalance() * 100
        ]);
    }

    public function withdraw(WithdrawRequest $request)
    {

        return $this->respondOk(200, null, [
            'Balance' => 0,
            'CasinoTransferId' => 0
        ]);
    }

    public function deposit(DepositRequest $request)
    {

        return $this->respondOk(200, null, [
            'Balance' => 0,
            'CasinoTransferId' => 0
        ]);
    }

    public function withdrawAndDeposit(WithdrawAndDepositRequest $request)
    {

        return $this->respondOk(200, null, [
            'Balance' => 0,
            'CasinoTransferId' => 0
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = null, array $payload = [])
    {

        list($message, $code) = array_values(CodeMapping::getByMeaning(CodeMapping::SUCCESS));

        $payload = array_merge($payload, [
            'ErrorCode' => $code,
            'ErrorMessage' => $message
        ]);

        return parent::respondOk($statusCode, '', $payload);
    }
}
