<?php
namespace App\Http\Controllers;
use App\Models\Article;
use Illuminate\Http\Request;
class ArticleController extends Controller {
 public function index() {
 $articles = Article::where('is_published', true)->orderBy('created_at', 'desc')->paginate(9);
 return view('front.article.index', compact('articles'));
 }
 public function show($slug) {
 $article = Article::where('slug', $slug)->where('is_published', true)->firstOrFail();
 $relatedArticles = Article::where('is_published', true)->where('id', '!=', $article->id)->inRandomOrder()->limit(3)->get();
 return view('front.article.show', compact('article', 'relatedArticles'));
 }
}
