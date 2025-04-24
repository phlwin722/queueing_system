<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class ManagerRequest extends FormRequest
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
        $id = $this->id ?: 'null';
        return [
            'manager_firstname' => ['required','regex:/^[a-zA-Z\s]+$/'],
            'manager_lastname' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'manager_username' => ['required','unique:managers,manager_username,' . $id . ',id'],
            'manager_password' => ['required'],
            'Image' => ['required'],
        ];
    }

    public function messages(): array
     {
        return [
            'manager_firstname.required' => 'The firstname field is required.',
            'manager_firstname.regex' => 'Invalid firstname! Only letters and spaces are allowed.',
            'manager_lastname.required' => 'The lastname field is required',
            'manager_lastname.regex' => 'Invalid lastname! Only letters and spaces are allowed.',
            'manager_username.required' => 'The username field is required',
            'manager_password.required' => 'The password field is requierd',
            'manager_status.required' => 'The status of manager is required',
            'Image.required' => 'The image field is required',  // Added message for image field
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
