<?php

namespace Model\Product;

use Model\Model;

class Tag extends Model
{

    public function tags()
    {
        return $this->fetch('tags')->desc('created_at')->execute();
    }

    public function new($values)
    {
        $this->insert("insert into tags set name = ? , image = ?, parent_id = ? ", [
            $values['name'],
            $values['image'],
            $values['parent']
        ]);
    }
}