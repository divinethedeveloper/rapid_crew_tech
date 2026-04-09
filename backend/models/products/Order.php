<?php

namespace Model\Product;

use Model\Model;

class Order extends Model
{

    public function new($values, $callback) {
        $sql =  "insert into orders set products = ?, quantities = ?, amount = ?, reference = ?, address = ?, lat = ?, lng = ?, user_id = ?, guest_id = ?, fullname = ?, email = ?, number = ?, note = ?";

        $values = [
            $values['products'],
            $values['quantities'],
            $values['amount'],
            $values['reference'],
            $values['address'],
            $values['lat'],
            $values['lng'],
            $values['user_id'],
            $values['guest_id'],
            $values['name'],
            $values['email'],
            $values['number'],
            $values['note'],
        ];

        $this->insert($sql, $values, $callback);
    }

    public function updateOrder($user_id, $guest_id, $order_id, $status, $replace="active") {
        $sql = " update orders set status = CONCAT( REPLACE(status, '$replace', ''), ? ) where id = ? and (user_id = ? or guest_id = ?) ";
        $this->insert($sql, [$status, $order_id, $user_id, $guest_id]);

        $sql = " update orders_to_products set status = CONCAT( REPLACE(status, '$replace', ''), ? ) where order_id = ? and (user_id = ? or guest_id = ?)";
        $this->insert($sql, [$status, $order_id, $user_id, $guest_id]);
    }

    public function order_to_product($order_id, $product_id, $reference, $quantity, $amount, $user_id, $guest_id) {
        $sql = "insert into orders_to_products set order_id = ?, product_id = ?, reference = ?, quantity = ?, amount = ?, user_id = ?, guest_id = ?";

        $this->insert($sql, [$order_id, $product_id, $reference, $quantity, $amount, $user_id, $guest_id]);
    }

    public function myOrders($user_id, $guest_id, $search) {
        $orders =  $this->fetch('orders')
                    ->subFetch('image', "( select image from products_to_images where product_id in (select product_id from orders_to_products where order_id = orders.id ) limit 1 )")
                    ->groupWhere([
                        ['where', 'user_id', '=', $user_id],
                        ['or', 'guest_id', '=', $guest_id],
                    ], true);

        if ($search['operator'] !== 'order' || preg_match_all('/[<=>]/', $search['operator'], $matches) > 0) {
            $orders = $orders->andWhere($search['target'], $search['operator'], $search['value']);
            // $this->error('it worked');
        }

        // $this->error('it did not worked');

        if ($search['value'] === 'asc') {
            $orders->asc($search['target']);
        }
        if ($search['value'] === 'desc') {
            $orders->desc($search['target']);
        }
        return $orders->paginate()->execute() ?? [];
    }

    public function myOrdersProducts($user_id, $guest_id, $id) {
        $orders = $this->fetch('orders_to_products')
                        ->subFetch('image', "( select image from products_to_images where products_to_images.product_id = orders_to_products.product_id limit 1 )")
                        ->subFetch('name', "( select name from products where products.id = orders_to_products.product_id limit 1 )")
                        ->where('order_id', $id)
                        ->groupWhere([
                            ['where', 'user_id', '=', $user_id],
                            ['or', 'guest_id', '=', $guest_id],
                        ], operator: 'and');

        Return $orders->paginate()->execute() ?? [];
    }

    public function order($id)
    {
        return $this->fetch('orders')->where('id', $id)->execute()[0];
    }

    public function orders($search) {
        $orders =  $this->fetch('orders')
                    ->subFetch('image', "( select image from products_to_images where product_id in (select product_id from orders_to_products where order_id = orders.id ) limit 1 )");

        if ($search['operator'] !== 'order' || preg_match_all('/[<=>]/', $search['operator'], $matches) > 0) {
            $orders = $orders->andWhere($search['target'], $search['operator'], $search['value']);
            // $this->error('it worked');
        }

        // $this->error('it did not worked');

        if ($search['value'] === 'asc') {
            $orders->asc($search['target']);
        }
        if ($search['value'] === 'desc') {
            $orders->desc($search['target']);
        }
        return $orders->paginate()->execute() ?? [];
    }

    public function ordersProducts($id) {
        $orders = $this->fetch('orders_to_products')
                        ->subFetch('image', "( select image from products_to_images where products_to_images.product_id = orders_to_products.product_id limit 1 )")
                        ->subFetch('name', "( select name from products where products.id = orders_to_products.product_id limit 1 )")
                        ->where('order_id', $id);

        Return $orders->paginate()->execute() ?? [];
    }

    public function myOrderTotals($user_id, $guest_id) {
        $totalOrders = $this->query("select count(*) as total, 
                                      (select count(*) from orders where status like '%active%'    and user_id = ? or guest_id = ?) as pending , 
                                      (select count(*) from orders where status like '%confirmed%' and user_id = ? or guest_id = ?) as confirmed,
                                      (select count(*) from orders where status like '%completed%' and user_id = ? or guest_id = ?) as completed
                                    from orders o where user_id = ? or guest_id = ?", [$user_id, $guest_id, $user_id, $guest_id, $user_id, $guest_id, $user_id, $guest_id]);

        return $totalOrders[0] ?? [];
    }

    public function orderTotals() {
        $totalOrders = $this->query("select count(*) as total, 
                                      (select count(*) from orders where status like '%active%' and orders.id = o.id) as pending , 
                                      (select count(*) from orders where status like '%confirmed%' and orders.id = o.id) as confirmed,
                                      (select count(*) from orders where status like '%completed%' and orders.id = o.id) as completed
                                    from orders o");

        return $totalOrders[0] ?? [];
    }

    public function search($user_id, $guest_id, $search)
    {
        $orders = $this->fetch('orders')
        ->subFetch('image', "( select image from products_to_images where product_id in (select product_id from orders_to_products where order_id = orders.id ) limit 1 )")
        ->groupWhere([
            ['where', 'user_id', '=', $user_id],
            ['or', 'guest_id', '=', $guest_id],
        ], true, operator: 'and');
        
        $orders->rawCondition(' and ( reference like ? ', ["%$search%"]);
        $orders->rawCondition(' or id in ( select order_id from orders_to_products where product_id in ( select id from products where name like  ? ) )) ', ["%$search%"]);

        return $orders->paginate(20)->execute();
    }

    public function adminSearch( $search)
    {
        $orders = $this->fetch('orders')
        ->subFetch('image', "( select image from products_to_images where product_id in (select product_id from orders_to_products where order_id = orders.id ) limit 1 )");
        
        $orders->rawCondition(' where ( reference like ? ', ["%$search%"]);
        $orders->rawCondition(' or id in ( select order_id from orders_to_products where product_id in ( select id from products where name like  ? ) )) ', ["%$search%"]);

        return $orders->paginate(20)->execute();
    }

}