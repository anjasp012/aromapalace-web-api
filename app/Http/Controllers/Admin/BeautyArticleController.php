<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BeautyArticle;
use App\Models\BeautyTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BeautyArticleController extends Controller
{
    public function index(): View
    {
        $articles = BeautyArticle::with('topic')->latest()->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        $topics = BeautyTopic::orderBy('name')->get();
        return view('admin.articles.create', compact('topics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic_id' => 'required|exists:beauty_topics,id',
            'summary' => 'required|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'required|url',
            'reading_time_minutes' => 'required|integer|min:1',
            'is_trending' => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['title']);
        if (BeautyArticle::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        $article = BeautyArticle::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'topic_id' => $validated['topic_id'],
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'cover_image' => $validated['cover_image'],
            'reading_time_minutes' => $validated['reading_time_minutes'],
            'is_trending' => !empty($validated['is_trending']),
            'is_published' => true,
            'published_at' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', "Artikel '{$article->title}' berhasil diterbitkan.");
    }

    public function edit(int $id): View
    {
        $article = BeautyArticle::findOrFail($id);
        $topics = BeautyTopic::orderBy('name')->get();
        return view('admin.articles.edit', compact('article', 'topics'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $article = BeautyArticle::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic_id' => 'required|exists:beauty_topics,id',
            'summary' => 'required|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'required|url',
            'reading_time_minutes' => 'required|integer|min:1',
            'is_trending' => 'nullable|boolean',
        ]);

        $article->update([
            'title' => $validated['title'],
            'topic_id' => $validated['topic_id'],
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'cover_image' => $validated['cover_image'],
            'reading_time_minutes' => $validated['reading_time_minutes'],
            'is_trending' => !empty($validated['is_trending']),
        ]);

        return redirect()->route('admin.articles.index')->with('success', "Artikel '{$article->title}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $article = BeautyArticle::findOrFail($id);
        $title = $article->title;
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', "Artikel '{$title}' telah dihapus.");
    }
}

