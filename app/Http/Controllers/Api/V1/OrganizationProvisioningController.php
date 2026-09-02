<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationProvisioningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Organization::with(['subscriptionPlan'])->withCount(['users', 'jobs', 'candidates'])->latest()->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'slug' => 'nullable|alpha_dash|unique:organizations,slug', 'subscription_plan_id' => 'required|exists:subscription_plans,id', 'admin_name' => 'required|string|max:255', 'admin_email' => 'required|email|unique:users,email', 'password' => 'required|string|min:10|confirmed', 'timezone' => 'required|timezone']);
        $result = DB::transaction(function () use ($data) {
            $organization = Organization::create(['name' => $data['name'], 'slug' => $data['slug'] ?? Str::slug($data['name']).'-'.Str::lower(Str::random(5)), 'subscription_plan_id' => $data['subscription_plan_id'], 'subscription_starts_at' => now(), 'subscription_ends_at' => now()->addMonth(), 'status' => 'active']);
            $admin = User::create(['organization_id' => $organization->id, 'name' => $data['admin_name'], 'email' => $data['admin_email'], 'password' => $data['password'], 'role' => 'organization_admin', 'status' => 'active', 'timezone' => $data['timezone']]);

            return compact('organization', 'admin');
        });

        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization): JsonResponse
    {
        return response()->json($organization->load(['subscriptionPlan', 'users']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $organization->update($request->validate(['name' => 'sometimes|string|max:255', 'subscription_plan_id' => 'sometimes|exists:subscription_plans,id', 'status' => 'sometimes|in:active,suspended,cancelled', 'subscription_ends_at' => 'sometimes|date|after:today']));

        return response()->json($organization->fresh()->load('subscriptionPlan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $organization->update(['status' => 'cancelled']);
        $organization->users()->update(['status' => 'inactive']);

        return response()->json(['message' => 'Organization access disabled.']);
    }
}
