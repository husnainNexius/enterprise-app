<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = \App\Models\Category::with(['parent', 'children']);
            
            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply status filter - handle string 'true'/'false' from URL params
            if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
                $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }
            
            // Apply parent filter
            if ($request->has('parent_id')) {
                if ($request->parent_id === null || $request->parent_id === '' || $request->parent_id === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }
            
            $categories = $query->orderBy('sort_order', 'asc')
                               ->orderBy('name', 'asc')
                               ->get();
            
            return CategoryResource::collection($categories)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(CategoryRequest $request)
    {
        try {
            $validated = $request->validated();
            $category = \App\Models\Category::create($validated);

            return response()->json(new CategoryResource($category), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = \App\Models\Category::with(['parent', 'children', 'products'])
                ->findOrFail($id);
            
            return response()->json(new CategoryResource($category), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = \App\Models\Category::findOrFail($id);
            
            // For partial updates (like status toggle), validate only the sent fields
            $rules = [];
            
            if ($request->has('name')) {
                $rules['name'] = 'required|string|max:255';
            }
            if ($request->has('slug')) {
                $rules['slug'] = 'required|string|max:255|unique:categories,slug,' . $id;
            }
            if ($request->has('description')) {
                $rules['description'] = 'nullable|string|max:2000';
            }
            if ($request->has('parent_id')) {
                $rules['parent_id'] = 'nullable|exists:categories,id|not_in:' . $id;
            }
            if ($request->has('sort_order')) {
                $rules['sort_order'] = 'required|integer|min:0';
            }
            if ($request->has('is_active')) {
                $rules['is_active'] = 'required|boolean';
            }
            if ($request->has('meta_title')) {
                $rules['meta_title'] = 'nullable|string|max:255';
            }
            if ($request->has('meta_description')) {
                $rules['meta_description'] = 'nullable|string|max:500';
            }
            
            $validated = $request->validate($rules);
            $category->update($validated);

            return response()->json(new CategoryResource($category), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = \App\Models\Category::findOrFail($id);
            
            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete category with subcategories. Please delete or move subcategories first.'
                ], 422);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete category with products. Please move or delete products first.'
                ], 422);
            }

            $category->delete();

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function tree()
    {
        try {
            $categories = \App\Models\Category::with(['children' => function($query) {
                $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

            return response()->json(CategoryResource::collection($categories), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'categories' => 'required|array',
                'categories.*.id' => 'required|exists:categories,id',
                'categories.*.sort_order' => 'required|integer|min:0',
                'categories.*.parent_id' => 'nullable|exists:categories,id',
            ]);

            foreach ($validated['categories'] as $categoryData) {
                $category = \App\Models\Category::find($categoryData['id']);
                $category->update([
                    'sort_order' => $categoryData['sort_order'],
                    'parent_id' => $categoryData['parent_id'] ?? null,
                ]);
            }

            return response()->json(['message' => 'Category order updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
