<?php

namespace Controller\Admin;

use Controller\Controller;
use Model\Product\Product;
use Model\Product\Category;
use Model\Product\Tag;
use Model\Product\Banner;
use Model\Product\Brand;
use Model\Product\Order;

class Posts extends Controller
{
    public function updateHirarchy()
    {
        $values = [
            'value'  => $_POST['value'] ?? '',
            'parent'  => $_POST['parent'] ?? '',
            'id'       => $_POST['id']   ?? '',
            'target'       => $_POST['target']   ?? '',
        ];

        [$empties, $values] = $this->clean_assoc($values);

        $values['parent'] = $values['parent'] == 'parent' ? '0' : $values['parent'];

        if(count($empties) > 0) {
            return [
                'status' => 'warning',
                'icon' => 'warning',
                'title'  => 'Empty Inputs',
                'text'   => 'Please provide all inputs and try again',
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('tag_'.time()), 'image');

        if (is_array($image_upload)) {
            $values['image'] = $image_upload[0];
        }

        $banner = new Banner();

        $banner->updateHirarchy($values);

        return [
            'status' => 'success',
            'icon' => 'success',
            'title'  => 'Updated successfully',
        ];
    }

    public function deleteHirarchy()
    {
        [$empty, $id] = $this->clean($_POST['id'] ?? '');
        [$empty1, $target] = $this->clean($_POST['target'] ?? '');

        if($empty || $empty1) {
            return [
                'status' => 'warning',
                'title' => 'System Busy',
                'message' => 'System is currently unavailable, please try again later'
            ];
        }

        $banner = new Banner();
        
        $banner->trashHirarchy($id, $target);

        return [
            'status' => 'success',
            'title' => 'Deleted Successfully',
            'message' => ''
        ];
    }

    public function createCategory()
    {
        $values = [
            'parent' => $_POST['parent'] ?? 'parent',
            'name' => $_POST['category'] ?? '',
            'image' => $_FILES['image']['tmp_name'] ?? ''
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0)
        {
            return [
                'status' => 'warning',
                'title' => 'Empty Inputs',
                'message' => "Please provide a cetegory name and image and try again",
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('category_'.time()), 'image');

        if (!isset($image_upload[0])) {
            $values['image'] = 'default_category.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $category = new Category();

        if($values['parent'] === 'parent') $values['parent'] = 0;

        $category->new($values);

        return [
            'status' => 'success',
            'title'  => 'Category Created Successfully',
            'message' => ''
        ];
    }

    public function createBlogCategory()
    {
        $values = [
            'parent' => $_POST['parent'] ?? 'parent',
            'name' => $_POST['category'] ?? '',
            'image' => $_FILES['image']['tmp_name'] ?? ''
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0)
        {
            return [
                'status' => 'warning',
                'title' => 'Empty Inputs',
                'message' => "Please provide a cetegory name and image and try again",
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('category_'.time()), 'image');

        if (!isset($image_upload[0])) {
            $values['image'] = 'default_category.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $category = new Category();

        if($values['parent'] === 'parent') $values['parent'] = 0;

        $category->newBlog($values);

        return [
            'status' => 'success',
            'title'  => 'Category Created Successfully',
            'message' => ''
        ];
    }

    public function createTag()
    {
        $values = [
            'parent' => $_POST['parent'] ?? 'parent',
            'name' => $_POST['tag'] ?? '',
            'image' => $_FILES['image']['tmp_name'] ?? ''
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0)
        {
            return [
                'status' => 'warning',
                'title' => 'Empty Inputs',
                'message' => "Please provide a tag name and image and try again",
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('tag_'.time()), 'image');

        if (!isset($image_upload[0])) {
            $values['image'] = 'default_tag.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $tag = new Tag();

        if($values['parent'] === 'parent') $values['parent'] = 0;

        $tag->new($values);

        return [
            'status' => 'success',
            'title'  => 'Tag Created Successfully',
            'message' => ''
        ];
    }

    public function createBrand()
    {
        $values = [
            'parent' => $_POST['parent'] ?? 'parent',
            'name' => $_POST['tag'] ?? '',
            'image' => $_FILES['image']['tmp_name'] ?? ''
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0)
        {
            return [
                'status' => 'warning',
                'title' => 'Empty Inputs',
                'message' => "Please provide a brand name and image and try again",
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('brand_'.time()), 'image');

        if (!isset($image_upload[0])) {
            $values['image'] = 'default_tag.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $brand = new Brand();

        if($values['parent'] === 'parent') $values['parent'] = 0;

        $brand->new($values);

        return [
            'status' => 'success',
            'title'  => 'Brand Created Successfully',
            'message' => ''
        ];
    }

    public function createBanner()
    {
        $values = [
            'title'    => $_POST['title']   ?? '',
            'content'  => $_POST['content'] ?? '',
            'location' => $_POST['location'] ?? '',
            'button'   => $_POST['button']  ?? '',
            'color'    => $_POST['color']   ?? '',
            'image'    => $_FILES['image']['tmp_name'] ?? '',
        ];

        if(strpos($values['location'], 'featured')) {
            $values = [
                'location' => $_POST['location'] ?? '',
                'image'    => $_FILES['image']['tmp_name'] ?? '',
            ];
        }
        
        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0) {
            return [
                'status' => 'warning',
                'title'  => 'Empty Inputs',
                'message'   => 'Please provide all inputs and try again',
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('tag_'.time()), 'image');

        if (!isset($image_upload[0])) {
            $values['image'] = 'default_banner.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $banner = new Banner();

        $banner->new($values);

        return [
            'status' => 'success',
            'title'  => 'Banner created successfully',
        ];

    }

    public function updateBanner()
    {
        $values = [
            'title'    => $_POST['title']   ?? '',
            'content'  => $_POST['content'] ?? '',
            'location' => $_POST['location'] ?? '',
            'button'   => $_POST['button']  ?? '',
            'color'    => $_POST['color']   ?? '',
            'id'       => $_POST['id']   ?? '',
        ];

        if(strpos($values['location'], 'featured')) {
            $values = [
                'location' => $_POST['location'] ?? '',
                'id'       => $_POST['id']   ?? '',
            ];
        }

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0) {
            return [
                'status' => 'warning',
                'icon' => 'warning',
                'title'  => 'Empty Inputs',
                'text'   => 'Please provide all inputs and try again',
            ];
        }

        $image_upload = $this->uploadFile('image', uniqid('tag_'.time()), 'image');

        if (is_array($image_upload)) {
            $values['image'] = $image_upload[0];
        }

        $banner = new Banner();

        $banner->update($values);

        return [
            'status' => 'success',
            'icon' => 'success',
            'title'  => 'Banner updated successfully',
        ];
    }

    public function deleteBanner()
    {
        [$empty, $id] = $this->clean($_POST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'warning',
                'title' => 'System Busy',
                'message' => 'System is currently unable to delete this product, please try again later'
            ];
        }

        $banner = new Banner();
        
        $banner->trash($id);

        return [
            'status' => 'success',
            'title' => 'Banner Deleted Successfully',
            'message' => ''
        ];
    }

    public function createProduct()
    {
        $values = [
            'name'              => $_POST['name'] ?? '',
            'description'       => $_POST['description'] ?? '',
            'categories'        => $_POST['categories'] ?? '',
            'tags'              => $_POST['tags'] ?? '',
            'brands'            => $_POST['brands'] ?? '',
            'product_price'     => $_POST['product_price'] ?? '',
            'selling_price'     => $_POST['selling_price'] ?? '',
            'product_quantity'  => $_POST['product_quantity'] ?? '',
            'rank'              => $_POST['rank'] ?? '',
            'gender'            => $_POST['gender'] ?? '',
            'status'            => $_POST['status'] ?? '',

        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0 ) {
            return [
                'status' => 'warning',
                'title'  => 'Empty Inputs',
                'message' => 'Please provide all inputs and try again.',
            ];
        }

        if(count($_FILES) <= 2 ) {
            return [
                'status' => 'warning',
                'title'  => 'Product Images',
                'message' => 'Please provide at least three (3) product images and try again',
            ];
        }


        $product = new Product();

        $values['description'] = $_POST['description'];

        $product->new($values, function ($product_id) use($product, $values) {

            foreach($_FILES as $name => $image) {

                $image_upload = $this->uploadFile($name, uniqid('product_'.time()), 'image');

                if (!isset($image_upload[0])) {
                    $upload_image = 'default_product.jpg';
                } else {
                    $upload_image = $image_upload[0];
                }

                $product->productToImage($product_id, $upload_image);
            }


            $categories = explode(',', $values['categories']);

            foreach($categories as $category) {
                $product->productToCategory($product_id, $category);
            }

            $tags = explode(',', $values['tags']);

            foreach($tags as $tag) {
                $product->productToTags($product_id, $tag);
            }

            $brands = explode(',', $values['brands']);

            foreach($brands as $brand) {
                $product->productToBrands($product_id, $brand);
            }


        });


        return [
            'status' => 'success',
            'title'  => 'Product Created Successfully',
            'message' => '',
            'data' => [
                'posts' => $_POST,
                'files' => $_FILES,
            ]
        ];
    }

    public function updateProduct()
    {
        $values = [
            'name'              => $_POST['name'] ?? '',
            'description'       => $_POST['description'] ?? '',
            'categories'        => $_POST['categories'] ?? '',
            'tags'              => $_POST['tags'] ?? '',
            'brands'            => $_POST['brands'] ?? '',
            'product_price'     => $_POST['product_price'] ?? '',
            'selling_price'     => $_POST['selling_price'] ?? '',
            'product_quantity'  => $_POST['product_quantity'] ?? '',
            'rank'              => $_POST['rank'] ?? '',
            'gender'            => $_POST['gender'] ?? '',
            'status'            => $_POST['status'] ?? '',
            'id'                => $_POST['id'] ?? '',
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0 ) {
            return [
                'status' => 'warning',
                'title'  => 'Empty Inputs',
                'message' => 'Please provide all inputs and try again.',
                // 'data' => $empties,
            ];
        }

        $values['removed_images'] = $this->clean($_POST['removed_images'] ?? '')[1];

        $product = new Product();

        $product_id = $values['id'];

        $values['description'] = $_POST['description'];

        $product->update($values);
        
        
        foreach($_FILES as $name => $image) {

            $image_upload = $this->uploadFile($name, uniqid('product_'.time()), 'image');

            if (!isset($image_upload[0])) {
                $upload_image = 'default_product.jpg';
            } else {
                $upload_image = $image_upload[0];
            }

            $product->productToImage($product_id, $upload_image);
        }

        
        $categories = explode(',', $values['categories']);

        
        $product->removeCategories($product_id);

        foreach($categories as $category) {
            $product->productToCategory($product_id, $category);
        }

        $tags = explode(',', $values['tags']);

        $product->removeTags($product_id);

        foreach($tags as $tag) {
            $product->productToTags($product_id, $tag);
        }

        $brands = explode(',', $values['brands']);

        $product->removeBrands($product_id);

        foreach($brands as $brand) {
            $product->productToBrands($product_id, $brand);
        }

        $removed_images = explode(',', $values['removed_images']);

        foreach($removed_images as $removed_image) {
            $product->removeImage($product_id, $removed_image);
        }



        return [
            'status' => 'success',
            'title'  => 'Product Updated Successfully',
            'message' => '',
            'data' => [
                'posts' => $_POST,
                'files' => $_FILES,
                'removed_images' => $removed_images,
                'tags' => $tags,
                'categories' => $categories,
            ]
        ];
    }

    public function deleteProduct()
    {
        [$empty, $id] = $this->clean($_POST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'warning',
                'title' => 'System Busy',
                'message' => 'System is currently unable to delete this product, please try again later'
            ];
        }


        $product = new Product();
        
        $product->trashProduct($id);

        return [
            'status' => 'success',
            'title' => 'Product Deleted Successfully',
            'message' => ''
        ];
    }
    public function updateOrder()
    {
        [$empty, $guest_id] = $this->clean($_REQUEST['guest_id'] ?? '');
        [$empty1, $user_id] = $this->clean($_REQUEST['user_id'] ?? '');



        if($empty && $empty1) return [
            'status' => 'error',
            'title' => 'System Busy',
            'message' => 'System is currently unable to update your order status, please try again later'
        ];

        [$empty, $status] = $this->clean($_REQUEST['status'] ?? '');
        [$empty1, $order_id] = $this->clean($_REQUEST['order_id'] ?? '');

        if($empty1 && $empty ) return [
            'status' => 'error',
            'title' => 'System Busy',
            'message' => 'System is currently unable to update your order status, please try again later'
        ];

        $order = new Order();

        $order->updateOrder($user_id, $guest_id, $order_id, 'completed');

        return [
            'status' => 'success',
            'title'  => 'Order Completed Successfully',
            'message' => ''
        ];

    }
}

