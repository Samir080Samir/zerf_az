<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;

class FileExistsInTmpFolder implements ValidationRule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $filePath = 'storage/images/' . $value;

        if (!File::exists($filePath)) {
            $fail("The file does not exist in the storage/tmp/ folder.");
        }
    }
}
