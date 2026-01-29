<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'parent' => $this->when($this->relationLoaded('parent'), function () {
                return $this->parent ? new self($this->parent) : null;
            }),
            
            'children' => $this->when($this->relationLoaded('children'), function () {
                return self::collection($this->children);
            }),
            
            'products_count' => $this->when($this->relationLoaded('products'), function () {
                return $this->products->count();
            }),
            
            'children_count' => $this->when($this->relationLoaded('children'), function () {
                return $this->children->count();
            }),
            
            'full_path' => $this->when($this->parent_id || $this->relationLoaded('parent'), function () {
                return $this->getFullPath();
            }),
            
            'has_children' => $this->when($this->relationLoaded('children'), function () {
                return $this->hasChildren();
            }),
        ];
    }
}
