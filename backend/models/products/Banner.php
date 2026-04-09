<?php

namespace Model\Product;

use Model\Model;

class Banner extends Model
{

    public function new($values)
    {
        $this->insert("insert into banners set title = ? , image = ?, content = ?, button = ?, color = ?, location = ? ", [
            $values['title']    ?? 'Featured Banner',
            $values['image'],
            $values['content']  ?? 'Featured Banner',
            $values['button']   ?? 'Featured Banner',
            $values['color']    ?? 'Featured Banner',
            $values['location']
        ]);
    }

    public function update($values)
    {
        $image = '';

        $update_values = [
            $values['title']    ?? 'Featured Banner',
            $values['content']  ?? 'Featured Banner',
            $values['button']   ?? 'Featured Banner',
            $values['color']    ?? 'Featured Banner',
            $values['location']
        ];

        if(isset($values['image'])) {
            array_push($update_values, $values['image']);
            $image = ', image = ?';
        }

        array_push($update_values, $values['id']);

        $this->insert("update banners set title = ? , content = ?, button = ?, color = ?, location = ? $image where id = ?", $update_values);
    }

    public function updateHirarchy($values)
    {
        $image = '';

        $target_map = [
            'categories' => 'categories',
            'blog_categories' => 'blog_categories',
            'tags' => 'tags',
            'brands' => 'brands',
        ];

        $target = $target_map[$values['target']];

        $update_values = [
            $values['parent'],
            $values['value'],
        ];

        if(isset($values['image'])) {
            array_push($update_values, $values['image']);
            $image = ', image = ?';
        }

        array_push($update_values, $values['id']);

        $this->insert("update $target set parent_id = ? , name = ? $image where id = ?", $update_values);
    }

    public function trash($id)
    {
        $this->insert("delete from banners where id = ?", [$id]);
    }

    public function trashHirarchy($id, $target)
    {
        $target_map = [
            'categories' => 'categories',
            'blog_categories' => 'blog_categories',
            'tags' => 'tags',
            'brands' => 'brands',
        ];

        $target = $target_map[$target];

        $this->insert("delete from $target where id = ?", [$id]);
    }
    

    public function banners($target = 'home')
    {
        return $this->fetch('banners')->where('location', $target)->execute();
    }

    public function hirarchies($target = 'categories')
    {
        $target_to_tables = [
            "categories" => "categories",
            "blog_categories" => "blog_categories",
            "tags" => "tags",
            "brands" => "brands",
        ];

        return $this->fetch($target_to_tables[$target])->execute();
    }
}