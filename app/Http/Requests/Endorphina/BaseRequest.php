<?php

namespace App\Http\Requests\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\SeamlessWalletCore\GameSession\Exceptions\SessionDoesNotExist;
use Illuminate\Http\Request;
use function app;
use function array_get;

class BaseRequest extends ApiRequest implements ApiValidationInterface
{

    use MetaDataTrait;

    protected $authAfterValidate = false;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        try {
            app('GameSession')->start(strtolower($request->input('token', '')));
        } catch (SessionDoesNotExist $e) {
            if ($this instanceof WinRequest || $this instanceof RefundRequest) {
                return true;
            }
            return false;
        }

        $userId = app('GameSession')->get('user_id');

        app('AccountManager')->selectAccounting(app('GameSession')->get('partner_id'), app('GameSession')->get('cashdesk_id'));

        return ($userId) ? true : false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
    }

    public function rules()
    {
        return [
            'token' => 'bail|required|string',
            'sign' => 'bail|required|string|check_sign',
        ];
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('500', array_get($firstError, 'message', 'Invalid input'), [
                'code' => array_get($firstError, 'code', StatusCode::SERVER_ERROR)
            ]
        );
    }
}
