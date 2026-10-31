<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
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
        return [
            // Data Perkara
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'reason' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:draft,submitted'],

            // Data Pasangan (spouse_detail)
            'spouse_detail' => ['required', 'array'],
            'spouse_detail.full_name' => ['required', 'string', 'max:255'],
            'spouse_detail.nik' => ['nullable', 'string', 'max:16'],
            'spouse_detail.birth_date' => ['nullable', 'date'],
            'spouse_detail.birth_place' => ['nullable', 'string', 'max:255'],
            'spouse_detail.gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'spouse_detail.religion' => ['nullable', 'string', 'max:50'],
            'spouse_detail.occupation' => ['nullable', 'string', 'max:100'],
            'spouse_detail.phone' => ['nullable', 'string', 'max:20'],
            'spouse_detail.address' => ['nullable', 'string'],

            // Data Pernikahan (marriage_detail)
            'marriage_detail' => ['required', 'array'],
            'marriage_detail.marriage_date' => ['nullable', 'date'],
            'marriage_detail.marriage_certificate_number' => ['nullable', 'string', 'max:100'],
            'marriage_detail.kua_or_civil_registry' => ['nullable', 'string', 'max:255'],
            'marriage_detail.has_children' => ['nullable', 'boolean'],
            'marriage_detail.children_count' => ['nullable', 'integer', 'min:0'],
            'marriage_detail.separation_date' => ['nullable', 'date'],
            'marriage_detail.last_address' => ['nullable', 'string'],
        ];
    }
}

