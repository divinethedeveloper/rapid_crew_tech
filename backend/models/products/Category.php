<?php

namespace Model\Product;

use Model\Model;

class Category extends Model
{

    public function categories()
    {
        return $this->fetch('categories')->asc('parent_id')->execute();
    }

    public function blogCategories()
    {
        return $this->fetch('blog_categories')->asc('parent_id')->execute();
    }

    public function new($values)
    {
        $this->insert("insert into categories set name = ? , image = ?, parent_id = ? ", [
            $values['name'],
            $values['image'],
            $values['parent']
        ]);
    }

    public function newBlog($values)
    {
        $this->insert("insert into blog_categories set name = ? , image = ?, parent_id = ? ", [
            $values['name'],
            $values['image'],
            $values['parent']
        ]);
    }

    public function filterCategories(string $target, string $operator, string $value, string $search = ''): array
    {
        $products = $this->fetch('categories')
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

    public function selectedCategories($id)
    {
        $sql = "
            WITH RECURSIVE CategoryHierarchy AS (
                SELECT id, image, name, parent_id, 0 AS sort_order
                FROM categories
                WHERE id = ?
                
                UNION ALL
                
                SELECT c.id, c.image, c.name, c.parent_id,
                    CASE
                        WHEN c.id = ? THEN 1
                        WHEN c.parent_id = ? THEN 2
                        WHEN c.parent_id = ch.id THEN 3
                        ELSE 4
                    END AS sort_order
                FROM categories c
                INNER JOIN CategoryHierarchy ch ON c.parent_id = ch.id
            )
            
            SELECT id, image, name, parent_id
            FROM CategoryHierarchy
          ORDER BY sort_order, id;          
        ";

        return $this->query($sql, [$id, $id, $id]);
    }
}