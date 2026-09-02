<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(SubscriptionPlan::orderBy('price_paise')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'slug' => 'required|alpha_dash|unique:subscription_plans,slug', 'price_paise' => 'required|integer|min:0', 'billing_interval' => 'required|in:month,year', 'features' => 'required|array|min:1', 'support_months' => 'integer|min:0', 'ai_level' => 'required|in:none,limited,extended', 'is_active' => 'boolean']);

        return response()->json(SubscriptionPlan::create($data), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        return response()->json($subscriptionPlan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string|max:100', 'price_paise' => 'sometimes|integer|min:0', 'features' => 'sometimes|array|min:1', 'support_months' => 'sometimes|integer|min:0', 'ai_level' => 'sometimes|in:none,limited,extended', 'is_active' => 'sometimes|boolean']);
        $subscriptionPlan->update($data);

        return response()->json($subscriptionPlan->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): Response
    {
        abort_if($subscriptionPlan->organizations()->exists(), 409, 'Plan is assigned to organizations.');
        $subscriptionPlan->delete();

        return response()->noContent();
    }
}
