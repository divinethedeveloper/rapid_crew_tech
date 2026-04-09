<?php

$router->post('/addToWaitlist', ['Controller\General\Posts', 'addToWaitlist']);
$router->any('/banners', ['Controller\General\Gets', 'banners']);
$router->any('/hirarchies', ['Controller\General\Gets', 'hirarchies']);
$router->any('/', ['Controller\General\Gets', 'debug']);

$router->any('/shop', ['Controller\General\Gets', 'shop']);
$router->any('/selectedCategories', ['Controller\General\Gets', 'selectedCategories']);
$router->any('/categoryProducts', ['Controller\General\Gets', 'categoryProducts']);
$router->any('/post', ['Controller\Blog\Gets', 'post']);
$router->any('/comments', ['Controller\Blog\Gets', 'comments']);
$router->any('/posts/search', ['Controller\Blog\Gets', 'searchPosts']);
$router->any('/posts', ['Controller\Blog\Gets', 'posts']);


$router->group(['before' => 'authenticate'], function () use ($router) {
    $router->any('/info', ['Controller\General\Gets', 'userInfo']);
    $router->post('/verifyAccount', ['Controller\General\Posts', 'verifyEmail']);
    $router->post('/sendEmailVerification', ['Controller\General\Posts', 'sendVerificationCode']);
    $router->any('/likePost', ['Controller\Blog\Posts', 'likePost']);
    $router->any('/comment', ['Controller\Blog\Posts', 'comment']);
    $router->any('/deleteComment', ['Controller\Blog\Posts', 'deleteComment']);


    $router->group(['before' => 'admin'], function () use ($router) {
        $router->any('/updateHirarchy', ['Controller\Admin\Posts', 'updateHirarchy']);
        $router->any('/deleteHirarchy', ['Controller\Admin\Posts', 'deleteHirarchy']);
        $router->any('/createCategory', ['Controller\Admin\Posts', 'createCategory']);
        $router->any('/createTag', ['Controller\Admin\Posts', 'createTag']);
        $router->any('/createBrand', ['Controller\Admin\Posts', 'createBrand']);
        $router->any('/createBanner', ['Controller\Admin\Posts', 'createBanner']);
        $router->any('/updateBanner', ['Controller\Admin\Posts', 'updateBanner']);
        $router->any('/deleteBanner', ['Controller\Admin\Posts', 'deleteBanner']);
        $router->any('/createProduct', ['Controller\Admin\Posts', 'createProduct']);
        $router->any('/updateProduct', ['Controller\Admin\Posts', 'updateProduct']);
        $router->any('/deleteProduct', ['Controller\Admin\Posts', 'deleteProduct']);
        $router->any('/productStats', ['Controller\Admin\Gets', 'productStats']);
        $router->any('/admin/products', ['Controller\Admin\Gets', 'products']);
        $router->any('/admin/product', ['Controller\Admin\Gets', 'product']);
        $router->any('/admin/products/search', ['Controller\Admin\Gets', 'searchProducts']);
        $router->post('/orderTotals', ['Controller\Admin\Gets', 'orderTotals']);
        $router->any('/admin/orders/search', ['Controller\Admin\Gets', 'searchOrders']);
        $router->post('/orders', ['Controller\Admin\Gets', 'orders']);
        $router->post('/ordersProducts', ['Controller\Admin\Gets', 'ordersProducts']);
        $router->post('/updateAdminOrder', ['Controller\Admin\Posts', 'updateOrder']);

        
    });

    $router->group(['before' => 'admin'], function () use ($router) {
        $router->any('/blogger', ['Controller\Blog\Gets', 'totals']);
        $router->any('/createBlogCategory', ['Controller\Admin\Posts', 'createBlogCategory']);
        $router->any('/createPost', ['Controller\Blog\Posts', 'createPost']);
        $router->any('/admin/posts', ['Controller\Blog\Gets', 'adminPosts']);
        $router->any('/admin/posts/search', ['Controller\Blog\Gets', 'searchAdminPosts']);
        $router->any('/admin/post', ['Controller\Blog\Gets', 'adminPost']);
        $router->any('/updatePost', ['Controller\Blog\Posts', 'updatePost']);

    });
});

$router->post('/createUser', ['Controller\General\Posts', 'createUser']);
$router->post('/login', ['Controller\General\Posts', 'login']);

$router->any('/categories', ['Controller\General\Gets', 'categories']);
$router->any('/blogCategories', ['Controller\General\Gets', 'blogCategories']);
$router->any('/brands', ['Controller\General\Gets', 'brands']);
$router->any('/tags', ['Controller\General\Gets', 'tags']);
$router->any('/products', ['Controller\General\Gets', 'products']);
$router->any('/product', ['Controller\General\Gets', 'product']);
$router->any('/relatedProducts', ['Controller\General\Gets', 'relatedProducts']);
$router->any('/fetchPrice', ['Controller\General\Gets', 'fetchPrice']);
$router->any('/products/search', ['Controller\General\Gets', 'searchProducts']);
$router->any('/orders/search', ['Controller\General\Gets', 'searchOrders']);
$router->any('/categories/search', ['Controller\General\Gets', 'searchCategories']);
$router->any('/brands/search', ['Controller\General\Gets', 'searchBrands']);

$router->any('/taggedProducts', ['Controller\General\Gets', 'taggedProducts']);


$router->post('/confirmPayment', ['Controller\General\Posts', 'confirmPayment']);
$router->post('/createOrder', ['Controller\General\Posts', 'createOrder']);
$router->post('/myOrders', ['Controller\General\Gets', 'myOrders']);
$router->post('/myOrdersProducts', ['Controller\General\Gets', 'myOrdersProducts']);
$router->post('/myOrderTotals', ['Controller\General\Gets', 'myOrderTotals']);
$router->post('/updateOrder', ['Controller\General\Posts', 'updateOrder']);
