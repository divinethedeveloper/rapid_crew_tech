<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BrandResource::collection(
            Brand::filter()->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $image = $request->file('image')->store('/brands');

        $parent = Brand::find($request->parent);

        if ($parent) $ancestors = "{{$parent->id}} $parent->ancestors";

        return new BrandResource(
            Brand::create([
                ...$request->all(),
                "image" => $image,
                "ancestors" => $ancestors ?? "",
            ])
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return new BrandResource($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $image = $request->file('image') ?
            $request->file('image')->store('/brands') :
            $brand->image;

        if ($request->parent) //check if there is a parent update
            $this->updateParent($request->parent, $brand);

        $brand->update([
            ...$request->all(),
            'image' => $image,
        ]);

        return new BrandResource($brand);
    }

    public function updateParent($parent, Brand $brand)
    {
        if ($parent == $brand->parent or $parent == $brand->id) return true; //check if the parent update is valid

        $parent = Brand::find($parent);

        if ($parent) $new_ancestors = "{{$parent->id}} $parent->ancestors";

        $children = Brand::where('ancestors', 'like', "%{{$brand->id}}%")->get();

        foreach ($children as $child) {
            $child->update([
                'ancestors' => str_replace($brand->ancestors, $new_ancestors, $child->ancestors)
            ]);
        }

        $brand->update([
            'ancestors' => $new_ancestors,
            'parent' => $parent->id,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        return $brand->delete();
    }
}
