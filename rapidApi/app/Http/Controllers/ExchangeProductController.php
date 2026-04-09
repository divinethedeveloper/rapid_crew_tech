<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExchangeProductRequest;
use App\Http\Requests\UpdateExchangeProductRequest;
use App\Http\Resources\ExchangeProductResource;
use App\Mail\ExchangeOrder;
use App\Models\ExchangeProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class ExchangeProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ExchangeProductResource::collection(
            ExchangeProduct::filter()->paginate()
        );
    }

    public function userProducts()
    {
        return ExchangeProductResource::collection(
            ExchangeProduct::filter()->where('user_id', Auth::user()->id)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExchangeProductRequest $request)
    {
        $product = ExchangeProduct::create([...$request->all(), 'user_id' => Auth::user()->id]);

        foreach ($request->file('images') as $image) {
            if ($path = $image->store('exchange_product/images')) {
                $product->update([
                    'image' => $path,
                ]);
            }
        }

        return new ExchangeProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExchangeProduct $exchangeProduct)
    {
        return new ExchangeProductResource($exchangeProduct);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExchangeProductRequest $request, ExchangeProduct $exchangeProduct)
    {
        $product = $exchangeProduct;
        $product->update([...$request->all()]);

        if ($request->file('images'))
            foreach ($request->file('images') as $image) {
                if ($path = $image->store('exchange_product/images')) {
                    $product->update([
                        'image' => $path
                    ]);
                }
            }

        return new ExchangeProductResource($product);    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExchangeProduct $exchangeProduct)
    {
        return $exchangeProduct->delete();
    }


    public function orderProduct(Request $request, ExchangeProduct $product) {
        $values = $request->validate([
            'name' => 'required',
            'number' => 'required|numeric',
            'email' => 'required|email'
        ]);

        Mail::to('rapidcrewtech@gmail.com')->send(new ExchangeOrder($product, [
            'name' => $values['name'],
            'number' => $values['number'],
            'email'  => $values['email']
        ], true));

        Mail::to($values['email'])->send(new ExchangeOrder($product, [
            'name' => $values['name'],
            'number' => $values['number'],
            'email'  => $values['email']
        ], false));
    }
}
