<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ContactCreateOrUpdateRequest extends BaseRequest
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
        $rules = [
            'name'  => ['required', 'max:50', 'regex:/^[a-zA-Z0-9 ]+$/'],
            'phone' => ['required', 'regex:/^\+90\d{10}$/'],       
        ];

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $rules['name'][] = Rule::unique('contacts', 'name')->ignore($this->contact->id);
        } else {
            $rules['name'][] = Rule::unique('contacts', 'name');
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => str_replace(' ', '', $this->phone),
        ]);
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must start with +90 followed by the 10 digit mobile number.',
        ];
    }
}
