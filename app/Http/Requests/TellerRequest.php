<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TellerRequest extends FormRequest
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
        $id = $this->id ?: 'NULL'; 

        return [
            'teller_firstname' => ['required','regex:/^[a-zA-Z\s]+$/','unique:tellers,teller_firstname,' . $id . ',id'],
            'teller_lastname' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'teller_username' => ['required'],
            'teller_password' => ['required'],
          //  'types_id' => ['required'], 
        ];
    }
    protected function failedValidation(Validator $validator){
        $errors = [];
        $messages = $validator->getMessageBag();

        foreach ($messages->keys() as $key) {
            $errors[$key] = $messages->get($key, $this->messageFormat)[0];
        }

        throw new HttpResponseException(response()->json($errors, 422));
    }
}