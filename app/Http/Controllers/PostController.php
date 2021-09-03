<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = app(Pipeline::class)
                ->send(Post::query())
                ->through([
                    \App\Filters\Active::class,
                    \App\Filters\Sort::class
                ])
                ->thenReturn()
                ->get();

        return response()->json([$posts]);
    }
}
