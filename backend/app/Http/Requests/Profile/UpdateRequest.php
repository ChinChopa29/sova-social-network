<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'string'],
            'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'phone' => ['sometimes', 'nullable', 'string', 'min:6', 'max:20', 'regex:/^\+?[0-9]{6,20}$/'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'language' => ['sometimes', 'string', 'in:en,ru'], // обязательно строка и одно из значений
            'is_public' => ['sometimes', 'boolean'],
            'is_verified' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->emptyToNull('name'),
            'gender' => $this->emptyToNull('gender'),
            'birthday' => $this->emptyToNull('birthday'),
            'bio' => $this->emptyToNull('bio'),
            'phone' => $this->emptyToNull('phone'),
            'website' => $this->emptyToNull('website'),
            'country' => $this->emptyToNull('country'),
            'city' => $this->emptyToNull('city'),
            'address' => $this->emptyToNull('address'),
        ]);
    }

    private function emptyToNull(string $key): mixed
    {
        return $this->has($key) && $this->input($key) === '' ? null : $this->input($key);
    }
}
