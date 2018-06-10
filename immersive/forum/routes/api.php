<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Auth\RegisterController@register')->name('register');
Route::post('/login', 'Auth\LoginController@login')->name('login');

Route::get('/posts', 'PostsController@posts')->name('posts');
Route::post('/new-post', 'PostsController@create')->name('create-post');
Route::put('/posts/{id}', 'PostsController@edit')->name('edit-post');
Route::delete('/posts/{id}', 'PostsController@delete')->name('delete-post');
