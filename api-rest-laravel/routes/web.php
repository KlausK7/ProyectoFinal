<?php
use App\Http\Middleware\ApiAuthMiddleware;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Ejemplos
Route::get('/', function () {
    return '<h1>Holas<h1>';
});

// Rutas de la Api Usuario

Route::post('/usuario/registro','UserController@register');
Route::post('/usuario/login','UserController@login');
Route::put('/usuario/update','UserController@update');
Route::post('/usuario/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/usuario/avatar/{filename}', 'UserController@getImage');
Route::get('/usuario/detail/{id}', 'UserController@detail');

//Rutas de la Api Categoria

Route::resource('/category', 'CategoryController');

//Rutas de la api PostController
Route::resource('/post', 'PostController');
Route::post('/post/upload','PostController@upload');
Route::get('/post/getImage/{filename}','PostController@getImage');
Route::get('/post/getPostsByCategory/{id}','PostController@getPostsByCategory');
Route::get('/post/getPostsByUser/{id}','PostController@getPostsByUser');