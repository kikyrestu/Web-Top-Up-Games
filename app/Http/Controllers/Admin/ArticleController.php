<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->latest();

        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('slug', 'like', '%' . $keyword . '%')
                    ->orWhere('content', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->query('status') === 'published') {
            $query->where('is_published', true);
        }

        if ($request->query('status') === 'draft') {
            $query->where('is_published', false);
        }

        $articles = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Article::count(),
            'published' => Article::where('is_published', true)->count(),
            'draft' => Article::where('is_published', false)->count(),
        ];

        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ];

        return view('admin.articles.index', compact('articles', 'stats', 'filters'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('image');
        $data['slug'] = $this->makeUniqueSlug($request->input('slug'), $request->input('title'));
        $data['content'] = $this->sanitizeHtmlContent((string) $request->input('content'));
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        Article::create($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,' . $article->id,
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('image');
        $data['slug'] = $this->makeUniqueSlug($request->input('slug'), $request->input('title'), $article->id);
        $data['content'] = $this->sanitizeHtmlContent((string) $request->input('content'));
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function makeUniqueSlug(?string $requestedSlug, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug(trim((string) ($requestedSlug ?: $title)));
        if ($baseSlug === '') {
            $baseSlug = 'artikel';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function sanitizeHtmlContent(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><h2><h3><h4><blockquote><ul><ol><li><a>';
        $clean = strip_tags($html, $allowedTags);

        // Strip dangerous inline handlers and javascript: links.
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\son\w+=\'[^\']*\'/i', '', $clean) ?? $clean;
        $clean = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $clean) ?? $clean;
        $clean = preg_replace('/href\s*=\s*\'javascript:[^\']*\'/i', 'href="#"', $clean) ?? $clean;

        return trim($clean);
    }
}
