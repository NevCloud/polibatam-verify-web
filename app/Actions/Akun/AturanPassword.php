<?php

namespace App\Actions\Akun;

use Illuminate\Validation\Rules\Password;

/** NFR-02: minimal 8 karakter, mengandung huruf dan angka. */
trait AturanPassword
{
    /** @return array<int, mixed> */
    protected function aturanPassword(): array
    {
        return ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'];
    }
}
