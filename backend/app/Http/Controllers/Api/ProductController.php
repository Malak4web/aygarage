<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'options', 'images'])->where('is_active', true);

        // Filter by category slug
        if ($request->filled('category') && $request->query('category') !== 'all') {
            $catSlug = $request->query('category');
            $query->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug);
            });
        }

        // Search query q
        if ($request->filled('q')) {
            $search = '%' . trim($request->query('q')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('brand', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        // Filter by bike model (fits JSON array contains model code)
        if ($request->filled('model')) {
            $model = $request->query('model');
            $query->where(function ($q) use ($model) {
                $q->whereJsonContains('fits', $model)
                  ->orWhereNull('fits')
                  ->orWhere('fits', '[]')
                  ->orWhere('fits', 'like', "%{$model}%");
            });
        }

        // Filter by brand(s)
        if ($request->filled('brand')) {
            $brands = is_array($request->query('brand')) ? $request->query('brand') : [$request->query('brand')];
            $query->whereIn('brand', $brands);
        }

        // Filter by viscosity
        if ($request->filled('viscosity')) {
            $viscosities = is_array($request->query('viscosity')) ? $request->query('viscosity') : [$request->query('viscosity')];
            $query->whereIn('viscosity', $viscosities);
        }

        // Filter by type
        if ($request->filled('type')) {
            $types = is_array($request->query('type')) ? $request->query('type') : [$request->query('type')];
            $query->whereIn('type', $types);
        }

        // Filter by price range
        if ($request->filled('minPrice')) {
            $query->where('price', '>=', (int) $request->query('minPrice'));
        }
        if ($request->filled('maxPrice')) {
            $query->where('price', '<=', (int) $request->query('maxPrice'));
        }

        // Stock filter
        if ($request->boolean('inStock')) {
            $query->where('stock', '>', 0);
        }

        // Sale filter
        if ($request->boolean('onSale')) {
            $query->whereNotNull('old_price')->whereRaw('old_price > price');
        }

        // Best seller
        if ($request->boolean('bestSeller')) {
            $query->where('is_best_seller', true);
        }

        // Weekly offer
        if ($request->boolean('weeklyOffer')) {
            $query->where('is_weekly_offer', true);
        }

        // Sorting
        $sort = $request->query('sort', 'bestseller');
        switch ($sort) {
            case 'new':
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price_asc':
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc')->orderBy('review_count', 'desc');
                break;
            case 'best':
            case 'bestseller':
            default:
                $query->orderBy('is_best_seller', 'desc')
                      ->orderBy('rating', 'desc')
                      ->orderBy('sold_count', 'desc');
                break;
        }

        // Base query clone for facets for the current category scope
        $facetsQuery = Product::where('is_active', true);
        if ($request->filled('category') && $request->query('category') !== 'all') {
            $catSlug = $request->query('category');
            $facetsQuery->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug);
            });
        }
        $facetsProducts = $facetsQuery->get(['brand', 'viscosity', 'type', 'fits']);

        $facetBrands = [];
        $facetViscosities = [];
        $facetTypes = [];
        $facetModels = [];

        foreach ($facetsProducts as $fp) {
            if ($fp->brand) {
                $facetBrands[$fp->brand] = ($facetBrands[$fp->brand] ?? 0) + 1;
            }
            if ($fp->viscosity) {
                $facetViscosities[$fp->viscosity] = ($facetViscosities[$fp->viscosity] ?? 0) + 1;
            }
            if ($fp->type) {
                $facetTypes[$fp->type] = ($facetTypes[$fp->type] ?? 0) + 1;
            }
            if (!empty($fp->fits) && is_array($fp->fits)) {
                foreach ($fp->fits as $m) {
                    $facetModels[$m] = ($facetModels[$m] ?? 0) + 1;
                }
            }
        }

        $facets = [
            'brands' => $facetBrands,
            'viscosities' => $facetViscosities,
            'types' => $facetTypes,
            'models' => $facetModels,
        ];

        // If pagination is requested or default
        if ($request->filled('perPage') || $request->filled('page')) {
            $perPage = (int) $request->query('perPage', 12);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => ProductResource::collection($paginator->items()),
                'meta' => [
                    'total' => $paginator->total(),
                    'page' => $paginator->currentPage(),
                    'perPage' => $paginator->perPage(),
                    'lastPage' => $paginator->lastPage(),
                ],
                'facets' => $facets,
            ]);
        }

        $all = $query->get();

        return response()->json([
            'data' => ProductResource::collection($all),
            'meta' => [
                'total' => $all->count(),
                'page' => 1,
                'perPage' => $all->count(),
                'lastPage' => 1,
            ],
            'facets' => $facets,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'options', 'images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'المنتج غير موجود'], 404);
        }

        // Related products in the same category (5 items)
        $related = Product::with(['category', 'options', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(5)
            ->get();

        $product->related = $related;

        return response()->json(new ProductResource($product));
    }

    public function reviews(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) {
            return response()->json(['message' => 'المنتج غير موجود'], 404);
        }

        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(ReviewResource::collection($reviews));
    }
}
