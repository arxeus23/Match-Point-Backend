<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $credentials['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        if ($user->organization && $user->organization->status !== 'active') {
            throw ValidationException::withMessages(['email' => ['This organization is not active.']]);
        }

        return response()->json(['user' => $user->load('organization'), 'token' => $user->createToken('dashboard')->plainTextToken]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('organization'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out']);
    }

    public function createUser(Request $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'password' => 'required|string|min:10|confirmed', 'role' => 'required|in:organization_admin,recruiter,hiring_manager,interviewer', 'timezone' => 'required|timezone']);
        $organizationId = $actor->isSuperAdmin() ? $request->validate(['organization_id' => 'required|exists:organizations,id'])['organization_id'] : $actor->organization_id;
        abort_unless($actor->isSuperAdmin() || $actor->role === 'organization_admin', 403);

        return response()->json(User::create([...$data, 'organization_id' => $organizationId, 'status' => 'active']), 201);
    }
}
