<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'media_type' => 'required|in:image,video,embed,html',
            'image' => 'required_if:media_type,image|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'required_if:media_type,video|mimes:mp4,webm|max:20480',
            'media_content' => 'required_if:media_type,embed,html',
            'order' => 'nullable|integer'
        ]);

        $data = $request->only(['title', 'link', 'media_type', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        if ($request->media_type === 'image' && $request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
            $data['media_content'] = null;
        } elseif ($request->media_type === 'video' && $request->hasFile('video')) {
            $data['media_content'] = $request->file('video')->store('banners/videos', 'public');
            $data['image'] = null;
        } elseif (in_array($request->media_type, ['embed', 'html'])) {
            $data['media_content'] = $request->media_content;
            $data['image'] = null;
        }

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'media_type' => 'required|in:image,video,embed,html',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|mimes:mp4,webm|max:20480',
            'media_content' => 'required_if:media_type,embed,html',
            'order' => 'nullable|integer'
        ]);

        $data = $request->only(['title', 'link', 'media_type', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        if ($request->media_type === 'image') {
            if ($request->hasFile('image')) {
                if ($banner->image) Storage::disk('public')->delete($banner->image);
                if ($banner->media_type === 'video' && $banner->media_content) Storage::disk('public')->delete($banner->media_content);
                $data['image'] = $request->file('image')->store('banners', 'public');
                $data['media_content'] = null;
            } else {
                $data['image'] = $banner->image;
                $data['media_content'] = null;
            }
        } elseif ($request->media_type === 'video') {
            if ($request->hasFile('video')) {
                if ($banner->image) Storage::disk('public')->delete($banner->image);
                if ($banner->media_type === 'video' && $banner->media_content) Storage::disk('public')->delete($banner->media_content);
                $data['media_content'] = $request->file('video')->store('banners/videos', 'public');
                $data['image'] = null;
            } else {
                $data['media_content'] = $banner->media_type === 'video' ? $banner->media_content : null;
                $data['image'] = null;
            }
        } elseif (in_array($request->media_type, ['embed', 'html'])) {
            if ($banner->image) Storage::disk('public')->delete($banner->image);
            if ($banner->media_type === 'video' && $banner->media_content) Storage::disk('public')->delete($banner->media_content);
            $data['media_content'] = $request->media_content;
            $data['image'] = null;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        if ($banner->media_type === 'video' && $banner->media_content) {
            Storage::disk('public')->delete($banner->media_content);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}