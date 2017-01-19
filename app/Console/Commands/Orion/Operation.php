<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\Request;
use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use App\Facades\AppLog;
use App\Http\Requests\Validation\Orion\Validation;
use Exception;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Psr7\str;

/**
 * Description of Operation
 *
 * @author petroff
 */
trait Operation {

    public function handleError(string $message, $level, string $module, string $line) {
        AppLog::warning($message, 'orion', $module, $line);
        $this->error('Something went wrong!');
    }

    public function handleSuccess(array $dataSuccess) {
        AppLog::info('Success. Data: ' . print_r($dataSuccess, true), 'orion', __CLASS__, __LINE__);
        $this->info('succes');
    }

    public function make(Request $requestQueueData, Validation $validatorQueueData, $operationsProcessor, Request $requestResolveData, Validation $validatorResolveData) {

        try {
            $data = $requestQueueData->getData();
            $validatorQueueData->validateBaseStructure($data);
            $elements = $validatorQueueData->getData($elements);
            $handleCommitRes = $operationsProcessor->make($elements);
            $dataResponse = $requestResolveData->getData($handleCommitRes);
            $validatorResolveData->validateBaseStructure($dataResponse);
            return $this->handleSuccess($dataResponse);
        } catch (RequestException $re) {
            $message = 'Request has error.  Request: ' . str($re->getRequest());
            if ($re->hasResponse()) {
                $message .= " Response" . str($re->getResponse());
            }
            $this->handleError($message, 'warning', '', $re->getLine());
        } catch (CheckEmptyValidation $ve) {
            $this->handleSuccess(['message' => 'Source is empty']);
        } catch (Exception $ex) {
            $this->handleError($ex->getMessage(), 'errors', '', $ex->getLine());
        }
    }

}
