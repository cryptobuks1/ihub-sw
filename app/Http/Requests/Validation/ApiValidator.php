<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/8/16
 * Time: 11:54 AM
 */

namespace App\Http\Requests\Validation;


use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class ApiValidator extends Validator
{
    /**
     * Determine if the data passes the validation rules.
     *
     * @param bool $bail
     * @return bool
     */
    public function passes(bool $bail = false)
    {
        $this->messages = new MessageBag;

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {
            $attribute = str_replace('\.', '->', $attribute);

            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);

                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }

            if($bail && $this->messages->has($attribute)){
                break;
            }
        }

        // Here we will spin through all of the "after" hooks on this validator and
        // fire them off. This gives the callbacks a chance to perform all kinds
        // of other validation that needs to get wrapped up in this operation.
        foreach ($this->after as $after) {
            call_user_func($after);
        }

        return $this->messages->isEmpty();
    }
}