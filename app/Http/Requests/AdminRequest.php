<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
            'Username'=> [
                'required',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'Password' => [
                'required'
            ]
        ];
    }

    protected function failedValidation (Validator $validator) {
        $errors = [];
        $message = $validator->getMessageBag();

        foreach($message->keys() as $key) {
            $errors[$key] = $message->get($key,$this->messageFormat)[0];
        }
        throw new HttpResponseException (response()-> json($errors,422));
    }
}
