<?php

namespace App\Http\Requests\MicroGaming;

use iHubGrid\SeamlessWalletCore\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Integrations\MicroGaming\StatusCode;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseMicroGamingRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    protected $codeMapClass = CodeMapping::class;

    public function isFromTrustedProxy()
    {
        return true;
    }

    public function isSecureRequest()
    {
        if (!$this->isSecure()) {
            $this->addMetaField('methodName', 'https');

            throw new ApiHttpException('400', "Only https is allowed", CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        }
    }

    public function authorizeUser(Request $request){
        try{
            app('GameSession')->start($request->input('methodcall.call.token', ''));
        } catch (SessionDoesNotExist $e) {
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        $userId = app('GameSession')->get('user_id');

        app('AccountManager')->selectAccounting(app('GameSession')->get('partner_id'), app('GameSession')->get('cashdesk_id'));

        if(!$userId){
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        if(config('integrations.microgaming.use_secure_request', true)) {
            $this->isSecureRequest();
        }

        $config_user = config('integrations.microgaming.login_server');
        $config_password = config('integrations.microgaming.password_server');

        if($config_user == $request->input('methodcall.auth.login') && $config_password == $request->input('methodcall.auth.password'))
        {
            $this->authorizeUser($request);

            return true;
        }
        //dd(app()->environment());
        return false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('401', null, CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH));
    }

    public function rules(){ return []; }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => array_get($firstError, 'code', StatusCode::SERVER_ERROR)
            ]
        );
    }
}
