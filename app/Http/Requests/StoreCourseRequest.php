<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructor'  => ['required', 'string', 'max:255'],
            'start_date'  => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'      => 'Kurs başlığı zorunludur.',
            'instructor.required' => 'Eğitmen adı zorunludur.',
            'start_date.date'     => 'Geçerli bir tarih giriniz.',
        ];
    }
}
