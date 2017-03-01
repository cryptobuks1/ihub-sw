<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Requests\Validation\Orion;

use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use Illuminate\Validation\ValidationException;
use Validator;

/**
 * Description of Validation
 *
 * @author petroff
 */
abstract class Validation {

    protected $rulesStructures = [];
    protected $rulesElements = [];
    protected $nameCommitRollbackElement = 'a:QueueDataResponse';
    protected $nameElement = '';
    protected $rulesRollbackCommit = [
        'a:LoginName' => 'required',
        'a:UserId' => 'required',
        'a:ChangeAmount' => 'required',
        'a:TransactionCurrency' => 'required',
        'a:Status' => 'required',
        'a:RowId' => 'required',
        'a:TransactionNumber' => 'required',
        'a:GameName' => 'required',
        'a:DateCreated' => 'required',
        'a:MgsReferenceNumber' => 'required',
        'a:ServerId' => 'required',
        'a:MgsPayoutReferenceNumber' => 'required',
        'a:PayoutAmount' => 'required',
        'a:ProgressiveWin' => 'required',
        'a:TournamentId' => 'required',
        'a:RowIdLong' => 'required',
    ];
    protected $errors;

    abstract function getElements(array $data): array;

    public function prepareElement(array $data): array {
        if (isset($data[0])) {
            $dataT = $data;
        } else {
            $dataT[] = $data;
        }
        return $dataT;
    }

    protected function validate(array $data, array $rules): bool {

        $v = Validator::make($data, $rules);
        if ($v->fails()) {
            $failedRules = $v->failed();

            //look for rule checkEmpty 
            foreach ($failedRules as $elements => $failedRules) {
                if (isset($failedRules['CheckEmpty'])) {
                    throw new CheckEmptyValidation();
                }
            }
            $this->errors = $v->errors();
            throw new ValidationException($v);
        }
        return true;
    }

    public function validateBaseStructure(array $data): bool {
        Validator::extend('checkEmpty', function ($attribute, $value, $parameters, $validator) {
            return (is_array($value) && isset($value[$this->nameElement]));
        });
        
        $this->validate($data, $this->rulesStructures);
        $elements = $this->getData($data);
        foreach ($elements as $key => $value) {
            $this->validate($value, $this->rulesElements);
        }
        return true;
    }

    public function errors(): array {
        return $this->errors;
    }

    public function getData(array $data): array {
        $preElements = $this->getElements($data);
        return $this->prepareElement($preElements);
    }

}