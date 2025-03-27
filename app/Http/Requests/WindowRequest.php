<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WindowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'window_name' => ['required'],
            'teller_id' => ['required'],
            'type_id' => ['required'],
        ];
    }
    public function messages(): array
    {
       return [
           'teller_id.required' => 'The assign personel field is required',
           'type_id.required' => 'The window type field is required'
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