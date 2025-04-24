<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class TypeRequest extends FormRequest
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
        $id = $this->id ?: null;
    
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('types', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($id),
            ],
            'indicator' => ['required'],
            'serving_time' => ['required', 'numeric', 'min:1'],
            'branch_id' => ['required'],
        ];
    }
    public function messages(): array
    {
       return [
           'branch_id.required' => 'The branch name field is requierd',
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