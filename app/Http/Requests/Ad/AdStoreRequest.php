<?php

namespace App\Http\Requests\Ad;

use App\Rules\FileExistsInTmpFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->check() ? auth()->id() : null,
            'slug' => Str::slug($this->input('title'))
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:200'],
            'last_name' => ['sometimes','string','max:200'],
            'phone' => ['sometimes', 'string', 'max:200'],
            'email' => ['sometimes','email','max:200'],


            'category_id' => ['nullable','exists:categories,id'],
            'user_id' => ['nullable','exists:users,id'],
            'city_id' => ['nullable','exists:cities,id'],

            'slug' => ['nullable', 'string', 'max:200'],
            'title' => ['required','string','max:200'],
            'description' => ['required','string'],
            'price' => ['required','numeric'],

            'gallery' => ['required', 'array'],
            'gallery.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp,svg'],

            'filters' => ['nullable']
        ];
    }
}
