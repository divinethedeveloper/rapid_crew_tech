<?php

namespace App\Http\Resources;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $response = parent::toArray($request);

        $response['images'] = $this->images;

        $categories = explode(' ', str_replace("{", "", str_replace("}", "", $this->categories)));
        $categories = Category::whereIn('id', $categories)->get();
        $response['categories'] = $categories;

        $brands = explode(' ', str_replace("{", "", str_replace("}", "", $this->brands)));
        $brands = Brand::whereIn('id', $brands)->get();
        $response['brands'] = $brands;

        foreach ($response['images'] as &$image) {
            $image['image'] = env('UPLOAD_URL').$image['image'];
        }

        $response['image'] = $response['images'][0]['image'] ?? env('UPLOAD_URL').'products/product.jpg';

        return $response;
    }
}
