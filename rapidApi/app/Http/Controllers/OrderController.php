<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Paystack;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = new Order();

        if (isset($request->filters['name']['$contains'])) {
            // return $request->filters['name']['$contains'];
            $products = Product::where('name', 'like', "%{$request->filters['name']['$contains']}%")->get();
            $products = $products->pluck('id');

            $orders = OrderProduct::whereIn('product', $products)->get();
            $orders = $orders->pluck('order');
            $orders = Order::whereIn('id', $orders);
        }

        return OrderResource::collection($orders->paginate());
    }

    public function userOrders(Request $request)
    {
        $orders = new Order();

        if (isset($request->filters['name']['$contains'])) {
            // return $request->filters['name']['$contains'];
            $products = Product::where('name', 'like', "%{$request->filters['name']['$contains']}%")->get();
            $products = $products->pluck('id');

            $orders = OrderProduct::whereIn('product', $products)->get();
            $orders = $orders->pluck('order');
            $orders = Order::whereIn('id', $orders);
        }

        return OrderResource::collection($orders->where('email', Auth::user()->email)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        return $this->storeOrder($request, Paystack::genTranxRef());
    }

    public function storeOrder($request, $reference) {
        $order = Order::create([
            ...$request->all(),
            'reference' => $reference
        ]);

        $total_price = 0;
        $total_quantity = 0;

        foreach ($request->products as $cart_product) {
            $product = Product::find($cart_product['id']);

            OrderProduct::create([
                'order' => $order->id,
                'product' => $product->id,
                'quantity' => $cart_product['quantity'],
                'price' => $product->price,
                'reference' => $reference,
            ]);

            $total_price += $cart_product['quantity'] * $product->price;
            $total_quantity += $cart_product['quantity'];
        }

        $order->update([
            'total_price' => $total_price,
            'total_quantity' => $total_quantity,
        ]);

        $order->password = $request->password;

        OrderCreated::dispatch($order);

        return new OrderResource($order);
    }

    /**
     * Display the s<tr>
     * <td>
     * <th colspan="">Total Quantity:</th>
     * <td>${{ number_format($order->total_quantity, 2) }}</td>
     * </td>.
     *
     * $order = Order::create([
     * ...$request->all(),
     * <td>
     * <th colspan="">Total Price:</th>
     * <td>${{ number_format($order->total_price, 2) }}</td>
     * </td>
     * </tr>pecified resource.
     */
    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function userOrder(Order $order)
    {
        if ($order->email == Auth::user()->email) {
            return new OrderResource($order);
        }
    }

    public function confirmOrder(OrderProduct $product)
    {
        $product->update([
            'status' => 'confirmed',
        ]);
    }

    public function rejectOrder(OrderProduct $product)
    {
        $product->update([
            'status' => 'rejected',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
    }
}
