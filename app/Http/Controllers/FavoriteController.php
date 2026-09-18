<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Geldige auto's
    |--------------------------------------------------------------------------
    |
    | De catalogus gebruikt momenteel auto-ID's 1 t/m 4.
    | Zo voorkomen we dat willekeurige ID's als favoriet worden opgeslagen.
    |
    */

    private function validCarIds(): array
    {
        return [
            1,
            2,
            3,
            4,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Favoriet toevoegen
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        int $id
    ): RedirectResponse {
        abort_unless(
            in_array($id, $this->validCarIds(), true),
            404
        );

        Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'car_id' => $id,
        ]);

        return back()->with(
            'success',
            'Auto toegevoegd aan je favorieten.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Favoriet verwijderen
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        int $id
    ): RedirectResponse {
        abort_unless(
            in_array($id, $this->validCarIds(), true),
            404
        );

        Favorite::where(
            'user_id',
            $request->user()->id
        )
            ->where(
                'car_id',
                $id
            )
            ->delete();

        return back()->with(
            'success',
            'Auto verwijderd uit je favorieten.'
        );
    }
}