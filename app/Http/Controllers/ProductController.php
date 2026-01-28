<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = \App\Models\Product::all(); // Get all products, not paginated
            return ProductResource::collection($products)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $validated = $request->validated();
            $product = \App\Models\Product::create($validated);

            return response()->json(new ProductResource($product), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = \App\Models\Product::findOrFail($id);
            return response()->json(new ProductResource($product), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $product = \App\Models\Product::findOrFail($id);
            $validated = $request->validated();
            $product->update($validated);

            return response()->json(new ProductResource($product), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = \App\Models\Product::findOrFail($id);
            $product->delete();

            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
