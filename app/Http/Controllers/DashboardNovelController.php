<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Services\SlugService;

class DashboardNovelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('author.novels.novel', [
            'title' => 'Novels',
            'novels' => Post::where('user_id', auth()->user()->id)->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('author.novels.create', [
            'title' => 'Create Novel',
            'categories' => Category::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request->file('images')->store('novel-images');

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts',
            'category_id' => 'required',
            'image' => 'image',
            'body' => 'required'
        ]);

        if ($request->file('image')) {
            $validatedData['image'] = $request->file('image')->store('novel-images');
        }

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['excerpt'] = Str::limit(strip_tags($request->body), 100);

        Post::create($validatedData);

        return redirect('/dashboard/post')->with('Success', 'Succesfully Add New Novel');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post) // nama variable harus sesuai nama model 
    {
        return view('author.novels.show', [
            'title' => 'Novel',
            'novel' => $post
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('author.novels.edit', [
            'title' => 'Edit Novel',
            'categories' => Category::all(),
            'novel' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $rules = [
            'title' => 'required|max:255',
            'category_id' => 'required',
            'image' => 'image',
            'body' => 'required'
        ];
        if ($request->slug != $post->slug) {
            $rules['slug'] = 'required|unique:posts';
        }

        $validatedData = $request->validate($rules);

        if ($request->file('image')) {
            if($post->image != null) Storage::delete($post->image);
            $validatedData['image'] = $request->file('image')->store('novel-images');
        }

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['excerpt'] = Str::limit(strip_tags($request->body), 100);

        Post::where('id', $post->id)
            ->update($validatedData);

        return redirect('/dashboard/post')->with('Success', 'Succesfully Update Novel');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if($post->image) Storage::delete($post->image);
        Post::destroy($post->id);
        return redirect('/dashboard/post')->with('Success', 'Novel Deleted');
    }

    public function checkSlug(Request $request)
    {
        $slug = SlugService::createSlug(Post::class, 'slug', $request->title);
        return response()->json(['slug' => $slug]);
    }
}
