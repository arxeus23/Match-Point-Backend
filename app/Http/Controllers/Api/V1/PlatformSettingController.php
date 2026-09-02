<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(PlatformSetting::firstOrCreate([]));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['platform_name' => ['required', 'string', 'max:255'], 'timezone' => ['required', 'timezone'], 'logo' => ['nullable', 'image', 'max:2048']]);
        $setting = PlatformSetting::firstOrCreate([]);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('platform', 'public');
        }
        unset($data['logo']);
        $setting->update($data);

        return response()->json($setting->fresh());
    }
}
