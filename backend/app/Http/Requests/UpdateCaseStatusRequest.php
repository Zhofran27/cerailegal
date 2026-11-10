<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseStatusRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                'in:draft,submitted,diajukan,in_review,diproses,registered,summons,mediation,in_court,sedang_sidang,approved,completed,selesai,rejected,ditolak',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'case_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('cases', 'case_number')->ignore($this->route('id')),
            ],
        ];
    }
}

