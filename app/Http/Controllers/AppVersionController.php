<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppVersionController extends Controller
{
    public function edit(): View
    {
        return view('app_versions.edit', [
            'appVersion' => AppVersion::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latest_version'            => ['required', 'string', 'max:50', 'regex:/^\d+(\.\d+)*$/'],
            'minimum_supported_version' => ['required', 'string', 'max:50', 'regex:/^\d+(\.\d+)*$/'],
            'force_update'              => ['nullable', 'boolean'],
            'update_message'            => ['nullable', 'string', 'max:1000'],
            'play_store_url'            => ['nullable', 'url', 'max:255'],
        ]);

        $validated['force_update'] = (bool) ($validated['force_update'] ?? false);

        AppVersion::current()->update($validated);

        return redirect()
            ->route('app-version.edit')
            ->with('status', 'App version settings updated successfully.');
    }
}
