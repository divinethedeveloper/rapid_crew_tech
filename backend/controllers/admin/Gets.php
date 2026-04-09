<?php

namespace Controller\Admin;

use Controller\Controller;
use Model\Product\Category;
use Model\Product\Product;
use Model\Product\Order;

class Gets extends Controller
{
    public function productStats()
    {
        $products = new Product();
        $totals = $products->totals();

        return [
            'products' => $totals['products'] ?? 0,
            'categories' => $totals['categories'] ?? 0,
            'out_of_stock' => $totals['out_of_stock'] ?? 0,
            'inventory_value' => $totals['inventory_value'] ?? 0,
            'totals' => $totals,
        ];
    }

    public function products()
    {
        [$empty, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        $product = new Product();

        $search = $this->searchEngine($type);

        return $product->filterAdminProducts(...$search);
    }

    public function product()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'success',
                'title'  => 'Oops',
                'message' => 'System busy, please try again later',
            ];
        }

        $product = new Product();

        $result = $product->adminProduct($id);

        $result['categories'] = $product->productCategories($id);
        $result['tags'] = $product->productTags($id);
        $result['brands'] = $product->productBrands($id);
        $result['images'] = $product->productImages($id);
        $result['image_endpoint'] = $this->image_endpoint;

        return $result;
    }

    public function searchProducts()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $product = new Product();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $product->filterAdminProducts(...$search_filter);

    }

    public function orderTotals()
    {
        $order = new Order();
        return $order->orderTotals();
    }

    public function orders()
    {
        $order = new Order();

        $search = $this->searchEngine($_REQUEST['type'] ?? 'recent');

        return $order->orders($search);
    }

    public function ordersProducts()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        $order = new Order();

        $result = $order->order($id);

        $result['data'] = $order->ordersProducts($id);
        $result['image_endpoint'] = $this->image_endpoint;

        return $result;
    }

    public function searchOrders()
    {
        [$empty, $search] = $this->clean(preg_replace('/^#/', '',$_REQUEST['search'] ?? '') ?? '');

        if($empty ) return [];
        
        $order = new Order();

        return $order->adminSearch( $search);
    }
    
}