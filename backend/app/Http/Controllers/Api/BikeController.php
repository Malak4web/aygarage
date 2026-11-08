<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsedBikeResource;
use App\Models\UsedBike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BikeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UsedBike::with(['inspections', 'images', 'serviceRecords'])
            ->whereIn('status', ['available', 'reserved']);

        // Model / Family
        if ($request->filled('family')) {
            $families = is_array($request->query('family')) ? $request->query('family') : [$request->query('family')];
            $query->whereIn('family', $families);
        }
        if ($request->filled('model')) {
            $model = $request->query('model');
            $query->where(function ($q) use ($model) {
                $q->where('bike_model_code', $model)
                  ->orWhere('model_name', 'like', "%{$model}%")
                  ->orWhere('family', $model);
            });
        }

        // Price range
        if ($request->filled('minPrice')) {
            $query->where('price', '>=', (int) $request->query('minPrice'));
        }
        if ($request->filled('maxPrice')) {
            $query->where('price', '<=', (int) $request->query('maxPrice'));
        }

        // Km range
        if ($request->filled('kmRange')) {
            $km = $request->query('kmRange');
            if ($km === 'under5k') {
                $query->where('km', '<', 5000);
            } elseif ($km === '5kTo20k' || $km === '5k-20k') {
                $query->whereBetween('km', [5000, 20000]);
            } elseif ($km === 'over20k') {
                $query->where('km', '>', 20000);
            }
        }

        // Min inspection score
        if ($request->filled('minScore')) {
            $query->where('inspection_score', '>=', (int) $request->query('minScore'));
        }

        // Year(s)
        if ($request->filled('year')) {
            $years = is_array($request->query('year')) ? $request->query('year') : [$request->query('year')];
            $query->whereIn('year', $years);
        }

        // Sorting
        $sort = $request->query('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('sold_count', 'desc')->orderBy('inspection_score', 'desc');
                break;
            case 'score-desc':
            case 'score_desc':
                $query->orderBy('inspection_score', 'desc');
                break;
            case 'price-asc':
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Facets calculation across all active/reserved bikes
        $allActive = UsedBike::whereIn('status', ['available', 'reserved'])->get(['family', 'km', 'inspection_score', 'year']);

        $facetFamilies = [];
        $facetKm = ['under5k' => 0, '5kTo20k' => 0, 'over20k' => 0];
        $facetScore = ['90+' => 0, '80-89' => 0];
        $facetYears = [];

        foreach ($allActive as $b) {
            if ($b->family) {
                $facetFamilies[$b->family] = ($facetFamilies[$b->family] ?? 0) + 1;
            }
            if ($b->km < 5000) {
                $facetKm['under5k']++;
            } elseif ($b->km <= 20000) {
                $facetKm['5kTo20k']++;
            } else {
                $facetKm['over20k']++;
            }
            if ($b->inspection_score >= 90) {
                $facetScore['90+']++;
            } elseif ($b->inspection_score >= 80) {
                $facetScore['80-89']++;
            }
            $facetYears[$b->year] = ($facetYears[$b->year] ?? 0) + 1;
        }

        $facets = [
            'families' => $facetFamilies,
            'kmRanges' => $facetKm,
            'scores' => $facetScore,
            'years' => $facetYears,
        ];

        if ($request->filled('perPage') || $request->filled('page')) {
            $perPage = (int) $request->query('perPage', 12);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => UsedBikeResource::collection($paginator->items()),
                'meta' => [
                    'total' => $paginator->total(),
                    'page' => $paginator->currentPage(),
                    'perPage' => $paginator->perPage(),
                    'lastPage' => $paginator->lastPage(),
                ],
                'facets' => $facets,
            ]);
        }

        $bikes = $query->get();

        return response()->json([
            'data' => UsedBikeResource::collection($bikes),
            'meta' => [
                'total' => $bikes->count(),
                'page' => 1,
                'perPage' => $bikes->count(),
                'lastPage' => 1,
            ],
            'facets' => $facets,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $bike = UsedBike::with(['inspections', 'images', 'serviceRecords'])
            ->where('slug', $slug)
            ->first();

        if (!$bike) {
            return response()->json(['message' => 'الموتوسيكل غير موجود'], 404);
        }

        // Similar bikes (up to 3 items)
        $similar = UsedBike::with(['inspections', 'images'])
            ->where('id', '!=', $bike->id)
            ->whereIn('status', ['available', 'reserved'])
            ->where(function ($q) use ($bike) {
                $q->where('family', $bike->family)
                  ->orWhere('id', '!=', $bike->id);
            })
            ->take(3)
            ->get();

        $bike->similar = $similar;

        return response()->json(new UsedBikeResource($bike));
    }
}
