<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
            'address' => ['nullable', 'string', 'max:2000'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_number' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'timezone'],
        ]);
        $request->user()->update($data);

        return response()->json($request->user()->fresh()->load('organization'));
    }
}
