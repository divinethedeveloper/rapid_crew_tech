<?php

namespace Controller\General;

use Controller\Controller;
use Model\User\User;
use Model\Product\Category;
use Model\Product\Tag;
use Model\Product\Product;
use Model\Product\Banner;
use Model\Product\Brand;
use Model\Product\Order;

class Gets extends Controller
{
    public function userInfo()
    {
        $user = new User();

        $this->startSession();

        $user_id = $this->decode($_SESSION['rapid_test']);

        $data = $user->user($user_id);

        $data['password'] = '';
        $data['id'] = '';

        return [
            'status' => 'success',
            'title' => 'User Information',
            'message' => 'Information retrieved successfully',
            'data' => $data,
        ];
    }

    public function brands()
    {
        $brand = new Brand();

        return $brand->brands();
    }

    public function categories()
    {
        $category = new Category();

        return $category->categories();
    }

    public function blogCategories()
    {
        $category = new Category();

        return $category->blogCategories();
    }

    public function tags()
    {
        $tag = new Tag();

        return $tag->tags();
    }

    public function mail()
    {
        return $this->sendEmail();
    }

    public function products()
    {
        [$empty, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        $product = new Product();

        $search = $this->searchEngine($type);

        return $product->filterProducts(...$search);
    }

    public function product()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        $product = new Product();

        $result = $product->product($id);

        if(count($result) <= 0 || $empty) {
            return [
                'status' => "error",
                'title'  => "Product Unavailable",
                'message'=> "This product is currently unavailable, please check again later"
            ];
        }

        $result['images'] = $product->productImages($id);
        $result['image_endpoint'] = $this->image_endpoint;

        return $result;
    }

    public function relatedProducts()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        $product = new Product();

        $result = $product->relatedProducts($id);

        return $result ?? [];
    }

    public function fetchPrice()
    {
        [$empties, $values] = $this->clean_assoc([
            'products' => $_REQUEST['products'],
            'quantities' => $_REQUEST['quantities'],
        ]);

        if(count($empties) > 0) {
            return [
                'status' => 'info',
                'title' => 'No Products In Cart',
                'message' => 'Please add some products to carts and try again!',
                'data' => $_REQUEST
            ];
        }

        $products = array_filter(explode(',', $values['products']), fn ($id) => $id !== "");
        $quantities = array_filter(explode(',', $values['quantities']), fn ($id) => $id !== "");

        $total = 0;
        $product = new Product();

        $index = 0;

        foreach($products as $id) {
            $item = $product->product($id);

            // $this->error($item['selling_price']);

            $sum_of_product = $item['selling_price'] * (int)$quantities[$index];

            $total += $sum_of_product;

            $index++;
        }

        return $total;
    }

    public function shop()
    {
        [ $empties, $values ] = $this->clean_assoc([
            'brands'     => $_REQUEST['brands']     ?? '',
            'categories' => $_REQUEST['categories'] ?? '',
            'gender'     => $_REQUEST['gender']     ?? '',
            'maxPrice'   => $_REQUEST['maxPrice']   ?? '',
            'minPrice'   => $_REQUEST['minPrice']   ?? '',
            'order'      => $_REQUEST['order']      ?? 'recent',
            'page'       => $_REQUEST['page']       ?? '',
        ]);

        $product = new Product();

        $search = $this->searchEngine($values['order']);

        $result = $product->filterProductsBase(...$search);

        $genders = array_filter(explode(',', $values['gender'] ?? ''), fn ($gender) => $gender !== "");

        $categories = array_filter(explode(',', $values['categories']), fn ($category) => $category !== "");
        $brands = array_filter(explode(',', $values['brands']), fn ($brand) => $brand !== "");

        if(count($categories) > 0 || count($brands) > 0) {

            $inject = '';
            $index = 0;

            if(count($categories) > 0) {
                $result->rawCondition(" and products.id in (select product_id from products_to_categories where category_id IN (SELECT id FROM CategoryHierarchy)) ");

                $result->query_pre_select .=  " WITH RECURSIVE CategoryHierarchy AS ( SELECT id FROM categories ";
            }
    
            foreach($categories as $category) {
                $index !== 0 ?  $inject = 'or' : $inject = 'where';


                $result->query_pre_select .= " $inject id = ? ";

                array_push($result->pre_query_parameters, $category);
    
                $index++;
            }

            if(count($categories) > 0) {
                $result->query_pre_select .=  " UNION ALL SELECT c.id FROM categories c JOIN CategoryHierarchy ch ON c.parent_id = ch.id ) ";
            }


            if(count($brands) > 0) {

                $result->rawCondition(" and products.id in (select product_id from products_to_brands where brand_id IN (SELECT id FROM BrandHierarchy)) ");

                if(count($categories) > 0) $result->query_pre_select .= ', ';
                else $result->query_pre_select = ' WITH RECURSIVE ';
                $result->query_pre_select .=  " BrandHierarchy AS ( SELECT id FROM brands ";
            }
    
            foreach($brands as $brand) {
                $index !== 0 ?  $inject = 'or' : $inject = 'where';


                $result->query_pre_select .= " $inject id = ? ";

                array_push($result->pre_query_parameters, $brand);
    
                $index++;
            }

            if(count($brands) > 0) {
                $result->query_pre_select .=  " UNION ALL SELECT c.id FROM brands c JOIN BrandHierarchy ch ON c.parent_id = ch.id ) ";
            }

        }

        $result->rawCondition(" and ( ");

        $result = $result->groupWhere([
            ['where', 'selling_price', '>=', $values['minPrice']],
            ['or',    'product_price', '>=', $values['minPrice']],
        ]);

        $result = $result->groupWhere([
            ['where', 'selling_price', '<=', $values['maxPrice']],
            ['or',    'product_price', '<=', $values['maxPrice']],
        ], operator: 'and');

        $result->rawCondition( " ) ");



        if(count($genders) > 0) {
            $result->rawCondition(" and ( ");

            $index = 0;

            if(count($genders) == 2 and ($genders[1] == 'male' or $genders[1] == 'female') and ($genders[0] == 'male' or $genders[0] == 'female') ) $genders = ['all'];

            foreach($genders as $gender) {
                $inject = $index > 0 ? 'or' : '';

                $result->rawCondition(" $inject gender = ? ", [$gender]);

                $index++;
            }

            $result->rawCondition( " ) ");

        }


        $result = $result->paginate()->execute();

        $result = [
            ...$result,
            // $values,
            // $categories,
            // $brands,
        ];


        return $result;
    }

    public function taggedProducts()
    {
        [$empty, $tag] = $this->clean($_REQUEST['tag'] ?? 'best_selling');

        $product = new Product();

        return $product->taggedProducts($tag);

    }

    public function banners()
    {
        $banner = new Banner();

        return $banner->banners($this->clean($_REQUEST['target'] ?? '')[1] ?? 'home');
    }

    public function hirarchies()
    {
        $banner = new Banner();

        return $banner->hirarchies($this->clean($_REQUEST['target'] ?? '')[1] ?? 'categories');
    }

    public function myOrders()
    {
        [$empty, $guest_id] = $this->clean($_REQUEST['guest'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        if($empty && $user_id == '') return [];


        $order = new Order();

        $search = $this->searchEngine($_REQUEST['type'] ?? 'recent');

        return $order->myOrders($user_id, $guest_id, $search);
    }

    public function myOrdersProducts()
    {
        [$empty, $guest_id] = $this->clean($_REQUEST['guest'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        if($empty && $user_id == '') return [];

        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        $order = new Order();

        return $order->myOrdersProducts($user_id, $guest_id, $id);
    }
    
    public function myOrderTotals()
    {
        [$empty, $guest_id] = $this->clean($_REQUEST['guest'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        if($empty && $user_id == '') return [];

        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        $order = new Order();

        return $order->myOrderTotals($user_id, $guest_id);
    }

    public function searchProducts()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $product = new Product();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $product->filterProducts(...$search_filter);

    }

    public function searchOrders()
    {
        [$empty, $search] = $this->clean(preg_replace('/^#/', '',$_REQUEST['search'] ?? '') ?? '');

        if($empty ) return [];

        [$empty, $guest_id] = $this->clean($_REQUEST['guest'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        if($empty && $user_id == '') return [];
        
        $order = new Order();

        return $order->search($user_id, $guest_id,  $search);
    }

    public function searchCategories()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $category = new Category();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $category->filterCategories(...$search_filter);

    }

    public function searchBrands()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $category = new Brand();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $category->filterBrands(...$search_filter);

    }

    public function selectedCategories()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty) return [];

        $category = new Category();

        // return ['shalom'];

        return $category->selectedCategories($id);
    }

    public function categoryProducts()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty) return [];

        $product = new Product();

        return $product->categoryProducts($id);
    }

    public function debug(){
        // $tag = new Tag();

        // $tag->openConnection();
        // $tag->closeConnection();

        // return [
        //     'connection' => isset($tag->connection->affected_rows)
        // ];
    }
}
