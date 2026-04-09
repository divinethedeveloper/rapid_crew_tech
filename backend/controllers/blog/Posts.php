<?php

namespace Controller\Blog;

use Controller\Controller;
use Model\Blog\Blog;

class Posts extends Controller
{
    public function createPost()
    {
        $values = [
            'title'              => $_POST['title'] ?? '',
            'description'       => $_POST['description'] ?? '',
            'categories'        => $_POST['categories'] ?? '',
            'content'            => $_POST['content'] ?? '',
            'status'            => $_POST['status'] ?? '',
        ];

        [$empties, $values] = $this->clean_assoc($values);

        $values['content'] = $_POST['content'];


        if(count($empties) > 0 ) {
            return [
                'status' => 'warning',
                'title'  => 'Empty Inputs',
                'message' => 'Please provide all inputs and try again.',
            ];
        }

        if(count($_FILES) == 0 ) {
            return [
                'status' => 'warning',
                'title'  => 'Cover Images',
                'message' => 'Please click browse provide a cover image and try again',
            ];
        }


        $blog = new Blog();

        $values['description'] = $_POST['description'];

        $image_upload = $this->uploadFile('image', uniqid('blog_'.time()), 'image');

        if (!is_array($image_upload)) {
            $values['image'] = 'default_blog.jpg';
        } else {
            $values['image'] = $image_upload[0];
        }

        $blog->new($values, function ($blog_id) use($blog, $values) {
            $categories = explode(',', $values['categories']);

            foreach($categories as $category) {
                $blog->postToCategory($blog_id, $category);
            }
        });


        return [
            'status' => 'success',
            'title'  => 'Blog Post Created Successfully',
            'message' => '',
        ];
    }

    public function updatePost()
    {
        $values = [
            'title'             => $_POST['title']       ?? '',
            'description'       => $_POST['description'] ?? '',
            'content'           => $_POST['content']     ?? '',
            'categories'        => $_POST['categories']  ?? '',
            'status'            => $_POST['status']  ?? '',
            'id'                => $_POST['id']          ?? '',
        ];

        [$empties, $values] = $this->clean_assoc($values);

        if(count($empties) > 0 ) {
            return [
                'status'  => 'warning',
                'title'   => 'Empty Inputs',
                'message' => 'Please provide all inputs and try again.',
                // 'data' => $empties,
            ];
        }

        $blog = new Blog();

        $blog_id = $values['id'];

        $image_upload = $this->uploadFile('image', uniqid('tag_'.time()), 'image');

        if (is_array($image_upload)) {
            $values['image'] = $image_upload[0];
        }

        $values['content'] = $_POST['content'];

        $blog->update($values);

        $categories = explode(',', $values['categories']);

        $blog->removeCategories($blog_id);

        foreach($categories as $category) {
            $blog->postToCategory($blog_id, $category);
        }

        return [
            'status' => 'success',
            'title'  => 'Post Updated Successfully',
            'message' => '',
        ];
    }

    public function deletePost()
    {
        [$empty, $id] = $this->clean($_POST['id'] ?? '');

        if($empty) {
            return [
                'status' => 'warning',
                'title' => 'System Busy',
                'message' => 'System is currently unable to delete this blog, please try again later'
            ];
        }


        $blog = new blog();
        
        $blog->trashPost($id);

        return [
            'status' => 'success',
            'title' => 'Post Deleted Successfully',
            'message' => ''
        ];
    }

    public function likePost()
    {
        [$empty,  $id] = $this->clean($_REQUEST['id'] ?? '');
        [$empty1, $state] = $this->clean($_REQUEST['state'] ?? 'false');

        if($empty || $empty1)
        {
            return [
                'status' => 'error',
                'title'  => 'Unable To Make Comment',
                'message' => 'please try again later',
            ];
        }

        $this->startSession();

        $user_id = $this->decode($_SESSION['rapid_test']);

        $blog = new Blog();

        if($state == 'true') {
            $blog->addLike($id, $user_id);
        }
        else {
            $blog->removeLike($id, $user_id);
        }

        return [
            'status' => 'success',
            'title'  => 'Like Made Successfully',
            'message' => '',
        ];
    }

    public function comment()
    {
        [$empty,  $id] = $this->clean($_REQUEST['id'] ?? '');
        [$empty1, $comment] = $this->clean($_REQUEST['comment'] ?? '');

        if($empty || $empty1)
        {
            return [
                'status' => 'error',
                'title'  => 'Empty Input',
                'message' => 'please type your comment and try again',
            ];
        }

        $this->startSession();

        $user_id = $this->decode($_SESSION['rapid_test']);

        $blog = new Blog();


        $blog->comment($id, $user_id, $comment);

        return [
            'status' => 'success',
            'title'  => 'Comment Made Successfully',
            'message' => '',
        ];
    }

    public function deleteComment()
    {
        [$empty,  $id] = $this->clean($_REQUEST['id'] ?? '');

        if($empty)
        {
            return [
                'status' => 'error',
                'title'  => 'Empty Input',
                'message' => '',
            ];
        }

        $this->startSession();

        $user_id = $this->decode($_SESSION['rapid_test']);

        $blog = new Blog();

        $blog->deleteComment($id, $user_id);

        return [
            'status' => 'success',
            'title'  => 'Comment Deleted Successfully',
            'message' => '',
        ];
    }
}