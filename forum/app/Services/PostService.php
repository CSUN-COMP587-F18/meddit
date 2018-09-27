<?php
namespace App\Services;

use App\Contracts\PostContract;
use App\Post;
use App\User;
use Validator;

class PostService implements PostContract {

    protected function validator(array $data){
        return Validator::make($data, [
            'user_id' => 'required|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:255'
        ]);
    }

    protected function save(array $data)
    {
        return Post::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'body' => $data['body']
        ]);
    }
    
    public function createPost($postData){
        $errors = $this->validator($postData)->errors();
        if( count($errors) == 0 ){
            $post = User::where('id', $postData['user_id'])->exists();
            if( $post ){
                $newpost = $this->save($postData);
                $newpost->comments;
                $newpost->user;
                return response()->json([ 'post' => $newpost ], 201);
            } else {
                return response()->json([
                    'errors' => [
                        'invalid' => 'User does not exist'
                    ]
                ], 401);
            }
        } else {
            return response()->json([ 'errors' => $errors ], 401);
        };
    }

    public function editPost($postData, $id){
        $post = Post::find($id);
        if( $post ){
            $errors = $this->validator($postData)->errors();
            if( count($errors) ) {
                return response()->json([
                    'errors' => $errors
                ], 401);
            } else {
                if( $post->user_id == $postData['user_id'] ){
                    $post->title = $postData['title'];
                    $post->body = $postData['body'];
                    $post->save();
                    return response()->json(['post' => $post], 201);
                } else {
                    return response()->json(['errors' => ['invalid' => 'You do not have permission to edit this post']], 401);
                }
            }
        } else {
            return response()->json([
                'errors' => [
                    'invalid' => 'Post not found'
                ]
            ], 401);
        }
    }

    public function deletePost($postData, $id){
        $post = Post::find($id);

        if( $post ){
            $user = User::where('id', $postData['user_id'])->get();
            if( ($post->user_id == $postData['user_id']) || ($user[0]->role == 1) ){
                $post->delete();
                return response()->json([
                    'message' => 'Post was successfully deleted',
                    'post' => $id
                ], 201);
            } else {
                return response()->json([
                    'errors' => [
                        'invalid' => 'You do not have permission to delete this post'
                    ]
                ], 401);
            }
        } else {
            return response()->json([
                'errors' => [
                    'invalid' => 'Post does not exist'
                ]
            ], 401);
        }
    }
}
?>