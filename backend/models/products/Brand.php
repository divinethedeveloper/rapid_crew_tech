<?php

namespace Model\Product;

use Model\Model;

class Brand extends Model
{

    public function brands()
    {
        return $this->fetch('brands')->desc('created_at')->execute();
    }

    public function new($values)
    {
        $this->insert("insert into brands set name = ? , image = ?, parent_id = ? ", [
            $values['name'],
            $values['image'],
            $values['parent']
        ]);
    }

    public function filterBrands(string $target, string $operator, string $value, string $search = ''): array
    {
        $products = $this->fetch('brands')
                     ->groupWhere([
                        ['where', 'name', 'like', "%$search%"],
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

}