<?php

namespace App\Http\Requests;

use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\FormRequest as DingoFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class Request extends DingoFormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->container['request'] instanceof \Illuminate\Http\Request) {
            throw new ResourceException($validator->errors()->first(), $validator->errors());
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

}