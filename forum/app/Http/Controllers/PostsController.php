<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Comment;
use App\UserRole;
use Validator;

use App\Contracts\PostContract;
use App\Contracts\UserContract;

class PostsController extends Controller
{
    protected $postRetriever = null;
    protected $userRetriever = null;

    public function __construct(PostContract $postRetriever, UserContract $userRetriever){
        $this->postRetriever = $postRetriever;
        $this->userRetriever = $userRetriever;
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_id' => 'required|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:255'
        ]);
    }

    protected function comments(Comment $comment) {
        $comment->comments;
        $comment->user;
        if( count($comment->comments) ){
            foreach ($comment->comments as $comment1) {
                $this->comments($comment1);
            }
        }
    }

    public function posts() {
        $posts = $this->postRetriever->getAllPosts();
        foreach ( $posts as $post){
            $post->user;
            $post->comments;
            foreach ($post->comments as $comment ){
                $this->comments($comment);
            }
        }

        return response()->json([
            'posts' => $posts
        ], 200);
    }

    public function post($id) {
        $post = $this->postRetriever->getPost($id);
        if( !$post ){
            return response()->json([
                'errors' => [
                    'invalid' => 'Post does not exist'
                ]
            ], 404);
        } else {
            $post->comments;
            $post->user;
            foreach ($post->comments as $comment ){
                $this->comments($comment);
            }
            return response()->json([
                'post' => $post
            ], 200);
        }
    }

    public function create(Request $request) {
        $errors = $this->validator($request->all())->errors();
        if( count($errors) == 0 ){
            $user_exists = $this->userRetriever->existsUser($request->input('user_id'));
            if( $user_exists ){
                $post = new Post();
                $post->title = $request->input('title');
                $post->body = $request->input('body');
                $post->user_id = $request->input('user_id');
                $created = $this->postRetriever->createPost($post);
                if( $created ){
                    $post->comments;
                    $post->user;
                    return response()->json([ 'post' => $post ], 201);
                } else {
                    return response()->json([
                        'errors' => [
                            'invalid' => 'Unable to create user.'
                        ]
                    ], 406);
                }
            } else {
                return response()->json([
                    'errors' => [
                        'invalid' => 'User does not exist'
                    ]
                ], 404);
            }
        } else {
            return response()->json([ 'errors' => $errors ], 400);
        };
    }

    public function edit(Request $request, $id) {
        $req = [
            'user_id' => $request->input('user_id'),
            'title' => $request->input('title'),
            'body' => $request->input('body')
        ];

        $post = $this->postRetriever->getPost($id);
        if( $post ){	
            $errors = validator($request->all())->errors();
            if( count($errors) ) {	
                return response()->json([	
                    'errors' => $errors	
                ], 400);	
            } else {	
                if( $post->user_id == $request->input('user_id') ){	
                    $edit = $this->postRetriever->editPost($post, $req);
                    if( $edit ){
                        return response()->json(['post' => $post], 202);
                    } else {
                        return response()->json(['errors' => ['invalid' => 'Unable to save changes']], 400);
                    }
                } else {	
                    return response()->json(['errors' => ['invalid' => 'You do not have permission to edit this post']], 401);	
                }	
            }	
        } else {	
            return response()->json([	
                'errors' => [	
                    'invalid' => 'Post not found'	
                ]	
            ], 404);	
        }
    }

    public function delete(Request $request, $id) {
        $post = $this->postRetriever->getPost($id);

        if( $post ){
            $user = $this->userRetriever->getUser($request->input('user_id'));
            if( ($post->user_id == $request->input('user_id')) || ($user->role == 1) ){
                $deleted = $this->postRetriever->deletePost($post);
                if( $deleted ){
                    return response()->json([
                        'message' => 'Post was successfully deleted',
                        'post' => $id
                    ], 200);
                } else {
                    return response()->json([
                        'errors' => [
                            'invalid' => 'Failed to delete this post'
                        ]
                    ], 400);
                }
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
            ], 404);
        }
    }
}
