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
            'teller_id' => ['required','unique:windows,teller_id'],
            'type_id' => ['required'],
        ];
    }
    protected function failedValidation(Validator $validator){
        $errors = [];
        $messages = $validator->getMessageBag();

                // Add custom message if the unique constraint fails
                if ($messages->has('teller_id')) {
                    $errors['teller_id'] = 'Already Assigned';
                } else {
                    foreach ($messages->keys() as $key) {
                        $errors[$key] = $messages->get($key, $this->messageFormat)[0];
                    }
                }

        throw new HttpResponseException(response()->json($errors, 422));
    }
}