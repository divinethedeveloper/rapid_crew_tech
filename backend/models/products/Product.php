<?php

namespace Model\Product;

use Model\Model;

class Product extends Model
{
    public function new($values, $callback)
    {
        $sql = " insert into products set  name = ?, description = ?, product_price = ?, selling_price = ?, quantity = ?, rank = ?, gender = ?, status = ? ";

        $values = [
            $values['name'],
            $values['description'],
            $values['product_price'],
            $values['selling_price'],
            $values['product_quantity'],
            $values['rank'],
            $values['gender'],
            $values['status'],
        ];

        $this->insert($sql, $values, $callback);
    }

    public function update($values)
    {
        $sql = " update products set  name = ?, description = ?, product_price = ?, selling_price = ?, quantity = ?, rank = ?, gender = ?, status = ? where id = ?";

        $values = [
            $values['name'],
            $values['description'],
            $values['product_price'],
            $values['selling_price'],
            $values['product_quantity'],
            $values['rank'],
            $values['gender'],
            $values['status'],
            $values['id'],
        ];

        $this->insert($sql, $values);
    }

    public function trashProduct($product_id)
    {
        $sql = " update products set status = 'deleted' where id = ?";

        $this->insert($sql, [$product_id]);
    }

    public function deleteProduct($product_id)
    {
        $sql = " delete from products where id = ? ";

        $this->query($sql, [$product_id], false);

        $this->removeCategories($product_id);
        $this->removeTags($product_id);
        $this->removeImage($product_id);
    }

    public function productToImage($product_id, $image)
    {
        $sql = " insert into products_to_images set product_id = ?, image = ? ";

        $this->insert($sql, [$product_id, $image]);
    }

    public function productToCategory($product_id, $category_id)
    {
        $previous = $this->fetchTotal('products_to_categories')
                         ->where('product_id', $product_id)
                         ->andWhere('category_id', $category_id)
                         ->execute();

        if($previous > 0) return false;

        $sql = " insert into products_to_categories set product_id = ?, category_id = ? ";

        $this->insert($sql, [$product_id, $category_id]);
    }

    public function productToTags($product_id, $tag_id)
    {
        $previous = $this->fetchTotal('products_to_tags')
                         ->where('product_id', $product_id)
                         ->andWhere('tag_id', $tag_id)
                         ->execute();

        if($previous > 0) return false;
        
        $sql = " insert into products_to_tags set product_id = ?, tag_id = ? ";

        $this->insert($sql, [$product_id, $tag_id]);
    }

    public function productToBrands($product_id, $brand_id)
    {
        $previous = $this->fetchTotal('products_to_brands')
                         ->where('product_id', $product_id)
                         ->andWhere('brand_id', $brand_id)
                         ->execute();

        if($previous > 0) return false;
        
        $sql = " insert into products_to_brands set product_id = ?, brand_id = ? ";

        $this->insert($sql, [$product_id, $brand_id]);
    }

    public function removeImage($product_id, $image_id)
    {
        $sql = " delete from products_to_images where product_id = ? and id = ? ";

        $this->query($sql, [$product_id, $image_id], false);
    }

    public function removeImages($product_id)
    {
        $sql = " delete from products_to_images where product_id = ? ";

        $this->query($sql, [$product_id], false);
    }

    public function removeCategories($product_id)
    {
        $sql = " delete from products_to_categories where product_id = ? ";

        $this->query($sql, [$product_id], false);
    }

    public function removeTags($product_id)
    {
        $sql = " delete from products_to_tags where product_id = ? ";

        $this->query($sql, [$product_id], false);
    }

    public function removeBrands($product_id)
    {
        $sql = " delete from products_to_brands where product_id = ? ";

        $this->query($sql, [$product_id], false);
    }

    public function totals()
    {
        $totals = $this->fetch('products', ['count(*) as products'])
                       ->subFetch('categories',      'select count(*) from categories where status != "deleted" ')
                       ->subFetch('out_of_stock',    'select count(*) from products where quantity <= 2 and status != "deleted" ')
                       ->subFetch('inventory_value', 'select sum(product_price * quantity) from products where status != "deleted" ')
                       ->where('status', '!=', 'deleted')
                       ->execute();
        return $totals[0] ?? [];
    }

    public function product($id)
    {
        $products = $this->fetch('products')
                         ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                         ->where('status', 'active')
                         ->andWhere('id', $id);


        return $products->execute()[0] ?? [];
    }

    public function relatedProducts($id)
    {
        $sql = "WITH RECURSIVE CategoryHierarchy AS (
                    SELECT id, parent_id
                    FROM categories
                    WHERE id in ( select products_to_categories.category_id from products_to_categories where products_to_categories.product_id = ? )
                    UNION ALL
                    SELECT c.id, c.parent_id
                    FROM categories c
                    INNER JOIN CategoryHierarchy ch ON c.parent_id = ch.id
                )
                SELECT DISTINCT p.*, (select image from products_to_images where products_to_images.product_id = p.id limit 1) as image
                FROM products p
                INNER JOIN products_to_categories pc ON p.id = pc.product_id
                INNER JOIN CategoryHierarchy ch ON pc.category_id = ch.id where p.status = 'active' ORDER BY RAND() limit 20";

        return $this->query($sql, [$id]) ?? [];
    }

    public function taggedProducts($tag)
    {
        $products = $this->fetch('products')
                         ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                         ->where('status', 'active')
                         ->rawCondition(" and id in (select product_id from products_to_tags where tag_id in (select id from tags where name like ? ) ) " , ["$tag"])
                         ->desc('rand(), rank desc, created_at');


        return $products->paginate()->execute() ?? [];
    }

    public function filterProducts(string $target, string $operator, string $value, string $search = ''): array
    {
        $products = $this->fetch('products')
                     ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                     ->groupWhere([
                        ['where', 'name', 'like', "%$search%"],
                        ['or', 'description', 'like', "%$search%"],
                        // ['or', 'location', 'like', "%$search%"],
                     ], true)
                     ->andWhere('status', 'active');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $products = $products->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $products->asc($target);
        }
        if ($value === 'desc') {
            $products->desc($target);
        }

        return $products->paginate()->execute();
    }

    public function filterProductsBase(string $target, string $operator, string $value, string $search = ''): object
    {
        $products = $this->fetch('products')
                     ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                     ->groupWhere([
                        ['where', 'name', 'like', "%$search%"],
                        ['or', 'description', 'like', "%$search%"],
                     ], true)
                     ->andWhere('status', 'active');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $products = $products->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $products->asc($target);
        }
        if ($value === 'desc') {
            $products->desc($target);
        }

        return $products;
    }

    public function filterAdminProducts(string $target, string $operator, string $value, string $search = ''): array
    {
        $products = $this->fetch('products')
                     ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                     ->groupWhere([
                        ['where', 'name', 'like', "%$search%"],
                        ['or', 'description', 'like', "%$search%"],
                        // ['or', 'location', 'like', "%$search%"],
                     ], true)
                     ->andWhere('status', '!=', 'deleted');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $products = $products->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $products->asc($target);
        }
        if ($value === 'desc') {
            $products->desc($target);
        }

        return $products->paginate()->execute();
    }

    public function adminProduct($id)
    {
        $product = $this->fetch('products')->where('id', $id)->andWhere('status', '!=', 'deleted')->execute();

        return $product[0] ?? [];
    }

    public function productCategories($id)
    {
        return $this->query('select * from categories where id in (select category_id from products_to_categories where product_id = ?) ', [$id]);
    }

    public function productTags($id)
    {
        return $this->query('select * from tags where id in (select tag_id from products_to_tags where product_id = ?) ', [$id]);
    }

    public function productBrands($id)
    {
        return $this->query('select * from brands where id in (select brand_id from products_to_brands where product_id = ?) ', [$id]);
    }

    public function productImages($id)
    {
        return $this->query('select * from products_to_images where product_id = ? ', [$id]);
    }

    public function categoryProducts($id)
    {

        $products = $this->fetch('products')
                         ->subFetch('image', 'select image from products_to_images where product_id = products.id limit 1')
                         ->rawCondition(' where id in ( select product_id from products_to_categories where category_id in ( select id from categoryHirarchy ) ) and status = "active" ');

        $products->query_pre_select = "
            with recursive categoryHirarchy as (
                select id from categories where id = ?

                union all

                select c.id from categories c join categoryHirarchy on c.parent_id = categoryHirarchy.id
            )
        ";

        $products->pre_query_parameters = [$id];

        return $products->paginate()->execute();
    }

    
}