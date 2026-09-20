<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * Tijdelijke vaste catalogus.
     *
     * Zolang auto's nog niet vanuit een Car-model/database komen,
     * blijft de catalogus hier centraal beheerd.
     *
     * @var array<int, array{
     *     id: int,
     *     brand: string,
     *     model: string,
     *     year: int,
     *     type: string,
     *     fuel: string,
     *     price: int,
     *     image: string,
     *     summary: string
     * }>
     */
    private const CATALOG_CARS = [
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

    /**
     * Toon de favorieten van de ingelogde gebruiker.
     */
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $favoriteCarIds = Favorite::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->pluck('car_id')
            ->map(
                static fn (mixed $carId): int => (int) $carId
            )
            ->unique()
            ->values();

        $carsById = $this->catalogCars()
            ->keyBy(
                static fn (array $car): int => (int) $car['id']
            );

        $cars = $favoriteCarIds
            ->map(
                static fn (int $carId): ?array => $carsById->get($carId)
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

    /**
     * Voeg een auto toe aan de favorieten.
     */
    public function store(
        Request $request,
        int $id
    ): RedirectResponse {
        $this->ensureValidCarId($id);

        Favorite::query()->firstOrCreate([
            'user_id' => (int) $request->user()->id,
            'car_id' => $id,
        ]);

        return back()->with(
            'success',
            'Auto toegevoegd aan je favorieten.'
        );
    }

    /**
     * Verwijder een auto uit de favorieten.
     */
    public function destroy(
        Request $request,
        int $id
    ): RedirectResponse {
        $this->ensureValidCarId($id);

        Favorite::query()
            ->where(
                'user_id',
                (int) $request->user()->id
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

    /**
     * Geef de vaste catalogus terug als collection.
     *
     * @return Collection<int, array{
     *     id: int,
     *     brand: string,
     *     model: string,
     *     year: int,
     *     type: string,
     *     fuel: string,
     *     price: int,
     *     image: string,
     *     summary: string
     * }>
     */
    private function catalogCars(): Collection
    {
        return collect(
            self::CATALOG_CARS
        );
    }

    /**
     * Controleer of een catalogus-ID bestaat.
     */
    private function ensureValidCarId(
        int $id
    ): void {
        abort_unless(
            $this->catalogCars()->contains(
                static fn (array $car): bool =>
                    (int) $car['id'] === $id
            ),
            404
        );
    }
}
