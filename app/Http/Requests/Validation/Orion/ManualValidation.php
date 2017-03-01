<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Requests\Validation\Orion;

/**
 * Description of CommitValidation
 *
 * @author petroff
 */
class ManualValidation extends Validation {

    function __construct() {
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.ManuallyValidateBetResponse' => 'required',
            's:Body.ManuallyValidateBetResponse.ManuallyValidateBetResult' => 'required',
        ];
    }

    public function getElements(array $data): array {
        return $data;
    }

}