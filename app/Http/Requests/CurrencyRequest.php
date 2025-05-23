<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_name' => ['required'],
            'currency_symbol' => ['required'],
            'buy_value' => ['required'],
            'sell_value' => ['required'],
            // 'branch_id' => ['required'],
        ];
    }

    // public function messages(): array
    // {
    //    return [
    //        'branch_id.required' => 'The Branch name field is required',
    //     ];
    //  }
    protected function failedValidation(Validator $validator){
        $errors = [];
        $messages = $validator->getMessageBag();

        foreach ($messages -> keys() as $key) {
            $errors[$key] = $messages->get($key, $this->messageFormat)[0];
        }
        throw new HttpResponseException(response()->json($errors,422));
        
    }
}