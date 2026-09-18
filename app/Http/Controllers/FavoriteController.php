<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Catalogusauto's
    |--------------------------------------------------------------------------
    |
    | De huidige Mashal-catalogus gebruikt deze vier vaste auto's.
    | Zodra auto's later uit de database worden beheerd, kan dit gedeelte
    | worden vervangen door een Car-model / inventory-query.
    |
    */

    private function catalogCars(): array
    {
        return [
            [
                'id' => 1,
                'brand' => 'Audi',
                'model' => 'A6 Sportback',
                'year' => 2024,
                'type' => 'Sedan',
                'fuel' => 'Hybrid',
                'price' => 48990,
                'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Premium comfort voor dagelijks rijden en lange reizen.',
            ],
            [
                'id' => 2,
                'brand' => 'Mercedes',
                'model' => 'C-Class',
                'year' => 2024,
                'type' => 'Sedan',
                'fuel' => 'Diesel',
                'price' => 44990,
                'image' => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Comfort, design en premium technologie in één auto.',
            ],
            [
                'id' => 3,
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2023,
                'type' => 'SUV',
                'fuel' => 'Petrol',
                'price' => 67850,
                'image' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Ruimte, stijl en krachtige prestaties voor elke route.',
            ],
            [
                'id' => 4,
                'brand' => 'Volkswagen',
                'model' => 'Golf GTI',
                'year' => 2024,
                'type' => 'Hatchback',
                'fuel' => 'Petrol',
                'price' => 34990,
                'image' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1000&q=80',
                'summary' => 'Sportieve stijl voor dagelijks gebruik en de weekendtrip.',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Geldige auto-ID's
    |--------------------------------------------------------------------------
    */

    private function validCarIds(): array
    {
        return array_map(
            static fn (array $car): int => (int) $car['id'],
            $this->catalogCars()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mijn favorieten
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $favoriteCarIds = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->pluck('car_id')
            ->map(
                static fn ($carId): int => (int) $carId
            )
            ->unique()
            ->values()
            ->all();

        $catalogCars = collect($this->catalogCars())
            ->keyBy(
                static fn (array $car): int => (int) $car['id']
            );

        $cars = collect($favoriteCarIds)
            ->map(
                static fn (int $carId) => $catalogCars->get($carId)
            )
            ->filter()
            ->values()
            ->all();

        return view(
            'site.favorites',
            [
                'cars' => $cars,
                'favoriteCount' => count($cars),
            ]
        );
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

        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('car_id', $id)
            ->delete();

        return back()->with(
            'success',
            'Auto verwijderd uit je favorieten.'
        );
    }
}
