@extends('layouts.admin')

@section('title', 'Buat Halaman')
@section('header', 'Buat Halaman Baru')

@section('content')
<div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6 max-w-4xl mx-auto">
    <form action="{{ route('admin.pages.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Judul Halaman</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Contoh: Syarat dan Ketentuan">
            @error('title')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Slug URL (Opsional)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono" placeholder="Biarkan kosong untuk generate otomatis dari judul">
            @error('slug')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Konten Halaman (HTML)</label>
            <textarea id="editor" name="content" rows="15" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono" placeholder="Ketikan struktur paragraf HTML di sini atau gunakan integrasi script...">{!! old('content') !!}</textarea>
            @error('content')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-dark-600 bg-dark-900 text-emerald-500 focus:ring-emerald-500/40 w-5 h-5">
                <span class="text-white font-bold">Publikasikan Halaman (Aktif)</span>
            </label>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl inset shadow-lg hover:-translate-y-0.5 transition">
                <i class="fas fa-save mr-2"></i> Simpan Halaman
            </button>
            <a href="{{ route('admin.pages.index') }}" class="text-gray-400 hover:text-white transition font-bold text-sm">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'lists link code table wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link code',
        skin: 'oxide-dark',
        content_css: 'dark',
        menubar: false
    });
</script>
@endpush
@endsection
