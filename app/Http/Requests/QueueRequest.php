<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class QueueRequest extends FormRequest
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
            "name" => [
                "required",
                "max:255"
            ],
            "email" =>  [
                'required',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
        ];
    }
    // "regex:/^(09|\+639)\d{9}$/"

    // public function messages(): array
    // {
    //     return [
    
    //         'mobile.regex' => 'Please input a valid mobile number.',
    //     ];
    // }

    protected function failedValidation(Validator $validator){
        $errors = [];
        $messages = $validator->getMessageBag();

        foreach ($messages -> keys() as $key) {
            $errors[$key] = $messages->get($key, $this->messageFormat)[0];
        }
        throw new HttpResponseException(response()->json($errors,422));
        
    }
}
