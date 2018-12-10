<?php
namespace App\Contracts;
use Illuminate\Http\Request;
use App\Post;

interface PostContract{
    public function createPost(Array $data): Post;
    public function getPost($id);
    public function getAllPosts();
    public function editPost(Post $post, $req);
    public function deletePost(Post $post);
    public function existsPost($id);
}
?>