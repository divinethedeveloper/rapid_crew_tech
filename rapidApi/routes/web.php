<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/order', function () {
    $user = [
        'name' => 'David Shalom',
    ];

    return view('order', [
        'order' => (object) [
            'email' => 'adedavid.tech@gmail.com',
            'phone_number' => '0501242312',
            'user' => (object) $user,
            'products' => (object) [
                (object) [
                    'name' => 'The Good Laptop',
                    'order_quantity' => 12,
                    'price' => 10,
                ],
            ],
            'total_price' => 120,
            'total_quantity' => 12,
        ],
        'link' => 'this mother fuckkkerrr',

        'admin' => true,
    ]);
});
