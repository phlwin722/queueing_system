<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
 
class BranchRequest extends FormRequest
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
        $id = $this->id ?: 'NuLL'; 
        return [
            'branch_name' => ['required'],
            'manager_name' => ['required'],
            'region' => ['required','unique:managers,manager_username,' . $id . ',id'],
            'province' => ['required'],
            'city' => ['required'],
            'Barangay' => ['required'],
            'address' => ['required'],
            'status' => ['required'],
            'opening_date' => ['required'],
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
