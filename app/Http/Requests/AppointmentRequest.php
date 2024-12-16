<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
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
            'name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'cpf' => 'sometimes|required|string|max:14',
            'arrival_date' => 'sometimes|required|date',
            'mother_name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required|string|max:5',
            'state' => 'sometimes|nullable|string|max:255',
            'birth_date' => 'required|date', 
            'city' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:15',
            'observation' => 'sometimes|nullable|string',
            'foreign_country' => 'sometimes|boolean',
            'no_phone' => 'sometimes|boolean',
            'gender' => [
                'required',
                Rule::in(['male', 'female', 'other'])
            ],
           /*  'photo' => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:2048', */

            'stay_duration' => 'sometimes|nullable|integer|min:1', 
        ];
    }
    
}
