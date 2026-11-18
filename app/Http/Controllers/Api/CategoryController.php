<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\PointOfInterestCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The filter chips above the list of places.
 */
class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            PointOfInterestCategory::query()->active()->ordered()->get()
        );
    }
}
