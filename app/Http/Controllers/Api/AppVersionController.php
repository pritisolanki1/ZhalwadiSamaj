<?php

namespace App\Http\Controllers\Api;

use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;

class AppVersionController extends ApiController
{
    public function show(): JsonResponse
    {
        $appVersion = AppVersion::current();

        return $this->successResponse('App version fetched', [
            'latest_version'            => $appVersion->latest_version,
            'minimum_supported_version' => $appVersion->minimum_supported_version,
            'force_update'              => (bool) $appVersion->force_update,
            'update_message'            => $appVersion->update_message,
            'play_store_url'            => $appVersion->play_store_url,
        ]);
    }
}
