<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CategoryResource::collection(
            Category::filter()->orderBy('score', 'desc')->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $image = $request->file('image')->store('/categories');

        $parent = Category::find($request->parent);

        if ($parent) $ancestors = "{{$parent->id}} $parent->ancestors";

        return new CategoryResource(
            Category::create([
                ...$request->all(),
                "image" => $image,
                "ancestors" => $ancestors ?? "",
            ])
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $image = $request->file('image') ?
            $request->file('image')->store('/categories') :
            $category->image;

        if ($request->parent) //check if there is a parent update
            $this->updateParent($request->parent, $category);

        $category->update([
            ...$request->all(),
            'image' => $image,
        ]);

        return new CategoryResource($category);
    }

    public function updateParent($parent, Category $category)
    {
        if ($parent == $category->parent or $parent == $category->id) return true; //check if the parent update is valid

        $parent = Category::find($parent);

        if ($parent) $new_ancestors = "{{$parent->id}} $parent->ancestors";

        $children = Category::where('ancestors', 'like', "%{{$category->id}}%")->get();

        foreach ($children as $child) {
            $child->update([
                'ancestors' => str_replace($category->ancestors, $new_ancestors, $child->ancestors)
            ]);
        }

        $category->update([
            'ancestors' => $new_ancestors,
            'parent' => $parent->id,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        return $category->delete();
    }
}
