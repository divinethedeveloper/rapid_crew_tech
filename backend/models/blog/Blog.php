<?php

namespace Model\Blog;

use Model\Model;

class Blog extends Model
{

    public function new($values, $callback)
    {
        $sql = " insert into posts set  title = ?, description = ?, image = ?, content = ?, status = ? ";

        $values = [
            $values['title'],
            $values['description'],
            $values['image'],
            $values['content'],
            $values['status'],
        ];

        $this->insert($sql, $values, $callback);
    }

    public function update($values)
    {
        

        $update_values = [
            $values['title'],
            $values['description'],
            $values['content'],
            $values['status'],
        ];

        $image = '';

        if(isset($values['image'])) {
            array_push($update_values, $values['image']);
            $image = ', image = ?';
        }

        array_push($update_values, $values['id']);

        $sql = " update posts set  title = ?, description = ?, content = ?, status = ? $image where id = ?";

        $this->insert($sql, $update_values);
    }

    public function trashPost($product_id)
    {
        $sql = " update posts set status = 'deleted' where id = ?";

        $this->insert($sql, [$product_id]);
    }

    public function postToCategory($product_id, $category_id)
    {
        $previous = $this->fetchTotal('posts_to_categories')
                         ->where('post_id', $product_id)
                         ->andWhere('category_id', $category_id)
                         ->execute();

        if($previous > 0) return false;

        $sql = " insert into posts_to_categories set post_id = ?, category_id = ? ";

        $this->insert($sql, [$product_id, $category_id]);
    }

    public function filterAdminPosts(string $target, string $operator, string $value, string $search = ''): array
    {
        $posts = $this->fetch('posts')
                     ->subFetch('likes', "( select count(*) from posts_to_likes where post_id = posts.id )")
                     ->subFetch('views', "( select count(*) from posts_to_views where post_id = posts.id )")
                     ->groupWhere([
                        ['where', 'title', 'like', "%$search%"],
                        ['or', 'description', 'like', "%$search%"],
                        // ['or', 'location', 'like', "%$search%"],
                     ], true)
                     ->andWhere('status', '!=', 'deleted');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $posts = $posts->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $posts->asc($target);
        }
        if ($value === 'desc') {
            $posts->desc($target);
        }

        return $posts->paginate()->execute();
    }

    public function filterPosts(string $target, string $operator, string $value, string $search = ''): array
    {
        $posts = $this->fetch('posts')
                     ->subFetch('likes', "( select count(*) from posts_to_likes where post_id = posts.id )")
                     ->subFetch('views', "( select count(*) from posts_to_views where post_id = posts.id )")
                     ->groupWhere([
                        ['where', 'title', 'like', "%$search%"],
                        ['or', 'description', 'like', "%$search%"],
                        // ['or', 'location', 'like', "%$search%"],
                     ], true)
                     ->andWhere('status', 'active');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $posts = $posts->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $posts->asc($target);
        }
        if ($value === 'desc') {
            $posts->desc($target);
        }

        return $posts->paginate()->execute();
    }

    public function filterCategoryPosts(string $target, string $operator, string $value, string $search = ''): array
    {
        $posts = $this->fetch('posts', ['id', 'title', 'description', 'image', 'status', 'created_at', 'updated_at'])
                     ->subFetch('likes', "( select count(*) from posts_to_likes where post_id = posts.id )")
                     ->subFetch('views', "( select count(*) from posts_to_views where post_id = posts.id )")
                     ->rawCondition(" where id in ( select post_id from posts_to_categories where category_id in (select id from blog_categories where name like ? ) ) ", ["%$search%"])
                     ->andWhere('status', 'active');

        if ($operator !== 'order' || preg_match_all('/[<=>]/', $operator, $matches) > 0) {
            $posts = $posts->andWhere($target, $operator, $value);
        }

        if ($value === 'asc') {
            $posts->asc($target);
        }
        if ($value === 'desc') {
            $posts->desc($target);
        }

        return $posts->paginate()->execute();
    }

    public function adminPost($id)
    {
        $product = $this->fetch('posts')->where('id', $id)->andWhere('status', '!=', 'deleted')->execute();

        return $product[0] ?? [];
    }

    public function post($id)
    {
        $product = $this->fetch('posts')
                        ->subFetch('likes', "( select count(*) from posts_to_likes where post_id = posts.id )")
                        ->subFetch('views', "( select count(*) from posts_to_views where post_id = posts.id )")
                        ->where('id', $id)->andWhere('status', '=', 'active')->execute();

        return $product[0] ?? [];
    }

    public function postCategories($id)
    {
        return $this->query('select * from blog_categories where id in (select category_id from posts_to_categories where post_id = ?) ', [$id]);
    }

    public function categories($id = 0)
    {

        $sql = "
            WITH RECURSIVE CategoryHierarchy AS (
                SELECT id, image, name, parent_id, 0 AS sort_order
                FROM blog_categories
                WHERE id in ( select category_id from posts_to_categories where post_id = ? )
                
                UNION ALL
                
                SELECT c.id, c.image, c.name, c.parent_id,
                    CASE
                        WHEN c.id in ( select category_id from posts_to_categories where post_id = ? ) THEN 1
                        WHEN c.parent_id in ( select category_id from posts_to_categories where post_id = ? ) THEN 2
                        WHEN c.parent_id = ch.id THEN 3
                        ELSE 4
                    END AS sort_order
                FROM blog_categories c
                INNER JOIN CategoryHierarchy ch ON c.parent_id = ch.id
            )
            
            SELECT id, image, name, parent_id
            FROM CategoryHierarchy
        ORDER BY sort_order, id;          
        ";

        return $this->query($sql, [$id, $id, $id]);

    }

    public function getCategories($name)
    {
        return $this->fetch('blog_categories', ['*', "
            CASE
                WHEN name = ? THEN 1
                ELSE 4
            END AS sort_order
        "])->rawCondition('', [$name])->asc('sort_order')->execute();
    }

    public function removeCategories($product_id)
    {
        $sql = " delete from posts_to_categories where post_id = ? ";

        $this->query($sql, [$product_id], false);
    }

    public function totalPosts()
    {
        return $this->fetchTotal('posts')->execute();
    }

    public function totalViews()
    {
        return $this->fetchTotal('posts_to_views')->execute();
    }

    public function totalLikes()
    {
        return $this->fetchTotal('posts_to_likes')->execute();
    }

    public function totalComments()
    {
        return $this->fetchTotal('post_comments')->execute();
    }

    public function addLike($id, $user_id)
    {
        $sql = " insert into posts_to_likes set post_id = ?, user_id = ? ";

        $this->insert($sql, [$id, $user_id]);
    }

    public function removeLike($id, $user_id)
    {
        $sql = " delete  from posts_to_likes where post_id = ? and user_id = ? ";

        $this->insert($sql, [$id, $user_id]);
    }

    public function addView($id, $user_id)
    {
        $sql = " insert into posts_to_views set post_id = ?, user_id = ? ";

        $this->insert($sql, [$id, $user_id]);
    }

    public function comment($id, $user_id, $comment)
    {
        $sql = "insert into post_comments set post_id = ?, user_id = ?, comment = ?";

        $this->insert($sql, [$id, $user_id, $comment]);
    }

    public function comments($id)
    {
        $comments = $this->fetch('post_comments')
                         ->subFetch("image", '( select media from users where id = post_comments.user_id  )')
                         ->subFetch("fullname", '( select fullname from users where id = post_comments.user_id  )')
                         ->where('post_id', $id)
                         ->desc('created_at')
                         ->paginate()
                         ->execute();

        return $comments;
    }

    public function deleteComment($id, $user_id)
    {
        $sql = " delete from post_comments where id = ? and (user_id = ? or (select role_id from users where id = ?) = 3 ) ";

        $this->insert($sql, [$id, $user_id, $user_id]);
    }
}