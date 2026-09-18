<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    /**
     * Toon de recente loginactiviteiten van de ingelogde gebruiker.
     */
    public function index(
        Request $request
    ): View {
        $limit = max(
            10,
            min(
                100,
                (int) config(
                    'login-security.history_limit',
                    50
                )
            )
        );

        $activities = LoginActivity::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->latest('logged_in_at')
            ->latest('id')
            ->limit($limit)
            ->get();

        $newDeviceCount = $activities
            ->where('is_new_device', true)
            ->count();

        return view(
            'site.security',
            [
                'activities' => $activities,
                'newDeviceCount' => $newDeviceCount,
            ]
        );
    }

    /**
     * Verwijder alleen de eigen loginhistorie.
     *
     * Dit verandert geen wachtwoord, sessie of OAuth-koppeling.
     */
    public function destroyHistory(
        Request $request
    ): RedirectResponse {
        LoginActivity::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->delete();

        return redirect()
            ->route('security.index')
            ->with(
                'success',
                'Je loginhistorie is verwijderd.'
            );
    }
}
