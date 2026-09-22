<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->is_blocked;
    }

    public function rules(): array
    {
        $maxPhotoKb = config('karsa.reports.max_photo_mb', 5) * 1024;
        $maxVideoKb = config('karsa.reports.max_video_mb', 20) * 1024;

        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0'],
            'type' => ['required', 'in:asap,api_kecil,api_besar,pembakaran_lahan'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'media' => ['required', 'array', 'min:1', 'max:3'],
            'media.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov',
                "max:{$maxVideoKb}",
            ],
            // Honeypot -- kolom tersembunyi, harus selalu kosong (CLAUDE.md §11.2).
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'Sertakan minimal satu foto atau video.',
            'media.*.mimes' => 'Format berkas harus JPG, PNG, WEBP, MP4, atau MOV.',
            'description.min' => 'Deskripsi terlalu singkat, jelaskan kondisi yang Anda lihat.',
            'website.prohibited' => 'Permintaan ditolak.',
        ];
    }
}
