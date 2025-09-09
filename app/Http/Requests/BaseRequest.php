<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BaseRequest extends FormRequest
{
    //use WebResponseHelper;
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     */
    protected $stopOnFirstFailure = true;

    /**
     * Default status for format
     *
     */
    private $status = 201;

    /**
     * Default success type for format
     *
     */
    private $success = false;


    /**
     * Custom format to send validation message
     *
     */
    protected function failedValidationFormat($validator): array
    {
        return [
            'success' => $this->success,
            'status'  => $this->status,
            'message' => $validator->errors()->first(),
        ];
    }

    /**
     * Overriding failed validation method.
     *
     */
    protected function failedValidation(Validator $validator)
    {
        if (!$this->ajax() && $this->session) {
            FormRequest::failedValidation($validator);
        }

        if ($this->ajax() && $this->session) {
            $this->status = 400;
            $response = $this->failedValidationFormat($validator);
            throw new HttpResponseException(response()->json($response, 422));
        }

        $response = $this->failedValidationFormat($validator);
        throw new HttpResponseException(response()->json($response, 200));
    }
}
