<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar erişebilir
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [];
    }
}
