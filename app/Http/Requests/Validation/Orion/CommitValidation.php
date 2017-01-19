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
class CommitValidation extends Validation {

    function __construct() {
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.GetCommitQueueDataResponse' => 'required',
            's:Body.GetCommitQueueDataResponse.GetCommitQueueDataResult' => 'checkEmpty',
        ];
        $this->rulesElements = $this->rulesRollbackCommit;
    }

    public function getData(array $data): array {
        $this->elements = $data['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult'][$this->nameCommitRollbackElement];
        if (isset($this->elements[0])) {
            $dataT = $this->elements;
        } else {
            $dataT[] = $this->elements;
        }
        return $dataT;
    }

}
