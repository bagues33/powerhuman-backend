<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'gender' => 'nullable|string|in:Male,Female',
            'age' => 'nullable|integer',
            'phone' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'role_id' => 'required|integer|exists:roles,id',
            'team_id' => 'required|integer|exists:teams,id',
        ];
    }
}