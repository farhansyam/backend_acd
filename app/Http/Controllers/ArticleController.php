<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::orderByDesc('created_at')->paginate(15);
        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.form', ['article' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'type'       => 'required|in:promo,tips',
            'color_hex'  => 'required|string|max:7',
            'is_active'  => 'boolean',
            'expired_at' => 'nullable|date',
            'image'      => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
        }

        Article::create([
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'content'    => $request->content,
            'type'       => $request->type,
            'color_hex'  => $request->color_hex,
            'is_active'  => $request->boolean('is_active', true),
            'expired_at' => $request->expired_at,
            'image'      => $imagePath,
        ]);

        return redirect()->route('articles.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        return view('articles.form', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'type'       => 'required|in:promo,tips',
            'color_hex'  => 'required|string|max:7',
            'is_active'  => 'boolean',
            'expired_at' => 'nullable|date',
            'image'      => 'nullable|image|max:2048',
        ]);

        $imagePath = $article->image;
        if ($request->hasFile('image')) {
            if ($article->image) Storage::disk('public')->delete($article->image);
            $imagePath = $request->file('image')->store('articles', 'public');
        }

        $article->update([
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'content'    => $request->content,
            'type'       => $request->type,
            'color_hex'  => $request->color_hex,
            'is_active'  => $request->boolean('is_active'),
            'expired_at' => $request->expired_at,
            'image'      => $imagePath,
        ]);

        return redirect()->route('articles.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) Storage::disk('public')->delete($article->image);
        $article->delete();
        return redirect()->route('articles.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    public function toggleActive(Article $article)
    {
        $article->update(['is_active' => !$article->is_active]);
        return back()->with('success', 'Status artikel diperbarui.');
    }
}
