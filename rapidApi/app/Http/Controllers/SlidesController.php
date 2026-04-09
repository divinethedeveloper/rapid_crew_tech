<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSlidesRequest;
use App\Http\Requests\UpdateSlidesRequest;
use App\Http\Resources\SlideResource;
use App\Models\Slides;

class SlidesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SlideResource::collection(Slides::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSlidesRequest $request)
    {
        $image = $request->file('image')->store('/slides');

        return new SlideResource(Slides::create([...$request->all(),
            'image' => $image,
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show($slides)
    {
        $slides = Slides::find($slides);

        return new SlideResource($slides);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSlidesRequest $request, Slides $slide)
    {
        $image = $request->file('image') ?
        $request->file('image')->store('/categories') :
        $slide->image;

        $slide->update([
            ...$request->all(),
            'image' => $image,
        ]);
        
        // return $slides;

        return new SlideResource($slide);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slides $slide)
    {
        $slide->delete();
    }
}
