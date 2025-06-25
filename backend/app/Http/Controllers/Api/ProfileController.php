<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, Profile $profile = null)
    {
        if (!$profile) {
            $profile = $request->user()->profile;
        }

        if (!$profile) {
            return response()->json(['message' => 'Профиль не найден'], 404);
        }

        $profile->load('user');

        return response()->json($profile);
    }

    public function update(UpdateRequest $request, Profile $profile, ProfileService $service)
    {
        $this->authorize('update', $profile);

        $data = $service->getUpdateData($request);
        $profile->update($data);

        return response()->json($profile);
    }

    public function destroy(Profile $profile)
    {
        $profile->delete();

        return response()->json(['message' => 'Профиль удален']);
    }
}
