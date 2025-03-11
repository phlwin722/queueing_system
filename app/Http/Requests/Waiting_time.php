<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Waiting_time extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "Waiting_time" => [
                "required",
                "numeric",        // Ensures the value is numeric
                "min:1",          // Ensures the value is at least 1
                "max:60",         // Ensures the value doesn't exceed 60
                "digits_between:1,2"  // Ensures the input is between 1 and 2 digits
            ],

            
            "Prepared" => [
                "required"
            ]
        ];
    }
    protected function failedValidation(Validator $validator){
        $errors = [];
        $messages = $validator->getMessageBag();

        foreach ($messages -> keys() as $key) {
            $errors[$key] = $messages->get($key, $this->messageFormat)[0];
        }
        throw new HttpResponseException(response()->json($errors,422));
        
    }
}
