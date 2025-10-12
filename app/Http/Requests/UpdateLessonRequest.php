<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string'],
            'video_url' => ['nullable', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Ders başlığı zorunludur.',
            'content.required' => 'Ders içeriği zorunludur.',
            'video_url.url'    => 'Video bağlantısı geçerli bir URL olmalıdır.',
        ];
    }
}
