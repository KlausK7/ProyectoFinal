<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;


class PruebaController extends Controller
{
    public function testOrm(){
        $post = Post::all();
        var_dump($post);
        die();
    }
}
