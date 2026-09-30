<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * De overzichtspagina met alle gepubliceerde posts, nieuwste eerst.
     */
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::published()->with(['author', 'categories'])->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Een losse post. Laravel zoekt hem op via de slug uit de URL
     * (zie Post::getRouteKeyName).
     */
    public function show(Post $post): View
    {
        // Een concept of een post met een datum in de toekomst hoort niet
        // zichtbaar te zijn, ook niet als je de URL raadt.
        abort_if($post->published_at === null || $post->published_at->isFuture(), 404);

        return view('posts.show', [
            'post' => $post->load(['author', 'categories']),
        ]);
    }
}
