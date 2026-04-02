<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::orderBy('name');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
        }

        $categories = $query->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:game,seluler,pc,voucher,pulsa,paket_data,pln,pdam,bpjs,internet,emoney,ppob',
            'publisher' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'input_fields' => 'nullable|json',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = ImageOptimizer::optimizeAndSave($request->file('thumbnail'), 'categories', 500, 85);
        }

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'publisher' => $request->publisher,
            'icon' => $request->icon,
            'thumbnail' => $thumbnailPath,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'is_popular' => $request->has('is_popular'),
            'is_new' => $request->has('is_new'),
            'sort_order' => $request->sort_order ?? 0,
            'input_fields' => $request->input_fields
                ? json_decode($request->input_fields, true)
                : Category::detectInputFields($request->type, $request->name),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');    
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:game,seluler,pc,voucher,pulsa,paket_data,pln,pdam,bpjs,internet,emoney,ppob',
            'publisher' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'input_fields' => 'nullable|json',
        ]);

        $thumbnailPath = $category->thumbnail;
        if ($request->hasFile('thumbnail')) {
            if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            $thumbnailPath = ImageOptimizer::optimizeAndSave($request->file('thumbnail'), 'categories', 500, 85);
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'publisher' => $request->publisher,
            'icon' => $request->icon,
            'thumbnail' => $thumbnailPath,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'is_popular' => $request->has('is_popular'),
            'is_new' => $request->has('is_new'),
            'sort_order' => $request->sort_order ?? 0,
            'input_fields' => $request->input_fields
                ? json_decode($request->input_fields, true)
                : Category::detectInputFields($request->type, $request->name),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');     
    }

    public function destroy(Category $category)
    {
        if ($category->thumbnail && Storage::disk('public')->exists($category->thumbnail)) {
            Storage::disk('public')->delete($category->thumbnail);
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');        
    }

    public function destroyBulk(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->selected_ids)->get();

        foreach ($categories as $category) {
            if ($category->thumbnail && Storage::disk('public')->exists($category->thumbnail)) {
                Storage::disk('public')->delete($category->thumbnail);
            }
            $category->delete();
        }

        return redirect()->route('admin.categories.index')->with('success', count($categories) . ' kategori berhasil dihapus secara massal.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        foreach ($request->order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
