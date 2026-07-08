<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers'                  => ['required', 'array'],
            'answers.*.question_id'    => ['required'],
            'answers.*.choice_id'      => ['required'],
            'answers.*.confidence'     => ['nullable', 'in:low,medium,high'],
            'time_spent_seconds'       => ['nullable', 'integer', 'min:0'],
        ];
    }
}
