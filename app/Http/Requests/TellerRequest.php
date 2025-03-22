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
            'teller_firstname' => ['required','regex:/^[a-zA-Z\s]+$/'],
            'teller_lastname' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
            'teller_username' => ['required','unique:tellers,teller_username,' . $id . ',id'],
            'teller_password' => ['required'],
            'type_ids_selected' => ['required', 'array', 'min:1'],  // Ensure it's an array and not empty
            'Image' => ['required'],
        ];
    }

    /**
     * Handle failed validation.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        $messages = $validator->getMessageBag();

        // Iterate through each validation error
        foreach ($messages->keys() as $key) {
            if ($key === 'type_ids_selected') {
                /* $errors[$key] = 'Personnel field is required'; */
                // If 'type_ids_selected' failed the 'array' rule
                if (in_array('array', $messages->get($key))) {
                    // If it fails array validation, override with custom message
                    $errors[$key] = 'Personnel field is required'; // Custom message for array validation failure
                } elseif (in_array('required', $messages->get($key))) {
                    // If 'required' rule failed (field is empty or missing)
                    $errors[$key] = 'Personnel field is required'; // Custom message
                } else {
                    // Use the default error message if the validation fails for any other reason
                    // $errors[$key] = $messages->get($key)[0];
                    $errors[$key] =  'Personnel field is required';
                }
            } else {
                // For other fields, keep the default error message
                $errors[$key] = $messages->get($key)[0];
            }
        }

        // Throw a response with the errors in JSON format (status 422 for validation errors)
        throw new HttpResponseException(response()->json($errors, 422));
    }
}
