<?php

namespace Controller\Blog;

use Controller\Controller;
use Model\Blog\Blog;
use Model\User\User;

class Gets extends Controller
{
    public function totals()
    {

        $blog = new Blog();

        return [
            'views'    => $blog->totalViews(),
            'likes'    => $blog->totalLikes(),
            'comments' => $blog->totalComments(),
            'posts'    => $blog->totalPosts(),
        ];
    }

    public function adminPosts()
    {
        [$empty, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        $blog = new Blog();
    
        $search = $this->searchEngine($type);

        return $blog->filterAdminPosts(...$search);
    }

    public function posts()
    {
        [$empty, $type] = $this->clean($_REQUEST['type'] ?? 'recent');
        [$empty, $name] = $this->clean($_REQUEST['name'] ?? '');

        $blog = new Blog();
    
        $search = $this->searchEngine($type);

        $search['search'] = $name;

        $result =  $blog->filterCategoryPosts(...$search);

        return [
            'status'     => 'success',
            'title'      => 'Posts Retrived Successfully',
            'data'       => $result,
            'categories' => $blog->getCategories($name),
            'image_endpoint'    => $this->image_endpoint,
            'message'    => '',
        ];
    }

    public function adminPost()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'success',
                'title'  => 'Oops',
                'message' => 'System busy, please try again later',
            ];
        }

        $blog = new Blog();

        $result = $blog->adminPost($id);

        $result['categories'] = $blog->postCategories($id);
        $result['image_endpoint'] = $this->image_endpoint;

        return $result;
    }

    public function post()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'error',
                'title'  => 'Oops',
                'message' => 'System busy, please try again later',
            ];
        }

        $blog = new Blog();

        $result = $blog->post($id);

        $result['categories'] = $blog->categories($id);
        $result['image_endpoint'] = $this->image_endpoint;

        return $result;
    }

    public function comments()
    {
        [$empty, $id] = $this->clean($_REQUEST['id'] ?? '');

        try{
            $user_id = $this->isLoggedIn();
        }
        catch(\Exception $e) {
            $user_id = '';
        }

        if($user_id === false) $user_id = '';

        $user = new User();
        $user = $user->user($user_id);

        if($empty) {
            return [
                'status' => 'error',
                'title'  => 'Oops',
                'message' => 'System busy, please try again later',
            ];
        }

        $blog = new Blog();

        $comments = $blog->comments($id);

        $index = 0;

        foreach($comments as $comment){

            if($comment['user_id'] == $user_id || (isset($user['role_id']) && ($user['role_id'] == '3' || $user['role_id'] == '1')))
            {
                $comments[$index]['is_user'] = true;
            }
            else {
                $comments[$index]['is_user'] = false;
            }

            $index++;
        }

        return $comments;
    }

    public function searchAdminPosts()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $blog = new Blog();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $blog->filterAdminPosts(...$search_filter);

    }

    public function searchPosts()
    {
        [$empty, $search] = $this->clean($_REQUEST['search'] ?? '');
        [$empty1, $type] = $this->clean($_REQUEST['type'] ?? 'recent');

        if($empty ) return [];
        
        $blog = new Blog();

        $search_filter = $this->searchEngine($type);

        $search_filter['search'] = $search;

        return $blog->filterPosts(...$search_filter);

    }
}