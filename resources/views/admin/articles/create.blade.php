@extends('layouts.admin')

@section('title', 'Tulis Artikel')
@section('header', 'Tulis Artikel')

@section('content')
<div class="space-y-5" x-data="articleForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Buat Artikel Baru</h1>
            <p class="text-sm text-gray-400">Kelola konten untuk halaman berita/promosi website.</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 rounded-xl bg-dark-700 text-gray-200 hover:bg-dark-600 text-sm font-semibold">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-900/20 border border-red-700/40 rounded-xl p-3 text-xs text-red-200">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl p-6">
        <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5" x-ref="form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Judul Artikel</label>
                    <input type="text" name="title" id="title" x-model="title" @input="syncSlug(); markDirty();" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" value="{{ old('title') }}" required>
                </div>
                <div>
                    <label for="slug" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Slug (Opsional)</label>
                    <input type="text" name="slug" id="slug" x-model="slug" @input="slugTouched = true; markDirty();" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" value="{{ old('slug') }}" placeholder="otomatis dari judul">
                </div>
            </div>

            <div>
                <label for="image" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Cover Artikel</label>
                <input type="file" name="image" id="image" @change="previewImage" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2">
                <p class="text-xs text-gray-500 mt-2">Format JPG, PNG, WEBP. Maksimal 2MB.</p>
                <div class="mt-3" x-show="imagePreview">
                    <img :src="imagePreview" class="w-48 h-28 object-cover rounded-lg border border-dark-600" alt="Preview Cover">
                </div>
            </div>

            <div>
                <label for="editor" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Konten Artikel</label>
                <div id="editor" class="bg-dark-900 border border-dark-600 text-white text-sm rounded-xl"></div>
                <textarea name="content" id="content" class="hidden" required>{{ old('content') }}</textarea>
                <p class="text-xs text-gray-500 mt-2">Gunakan editor untuk heading, list, bold, italic, quote, dan link.</p>
            </div>

            <div class="flex items-center justify-between border-t border-dark-700/60 pt-4">
                <div class="text-xs" :class="saveState === 'saved' ? 'text-emerald-400' : (saveState === 'saving' ? 'text-amber-400' : 'text-gray-400')" x-text="saveLabel"></div>

                <input type="hidden" name="is_published" id="is_published_input" value="{{ old('is_published', true) ? '1' : '0' }}">

                <div class="flex items-center gap-2">
                    <button type="button" x-show="hasDraft" @click="restoreDraft" class="px-4 py-2 rounded-xl bg-dark-700 text-gray-200 hover:bg-dark-600 text-sm font-semibold">Pulihkan Draft</button>
                    <button type="button" @click="previewArticle" class="px-4 py-2 rounded-xl bg-cyan-600/20 border border-cyan-500/30 text-cyan-300 hover:bg-cyan-600/30 text-sm font-semibold">Preview</button>
                    <button type="button" @click="submitForm(false)" class="px-4 py-2 rounded-xl bg-dark-700 text-gray-200 hover:bg-dark-600 text-sm font-semibold">Simpan Draft</button>
                    <button type="button" @click="submitForm(true)" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all">Publish</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    function articleForm() {
        return {
            title: @json(old('title', '')),
            slug: @json(old('slug', '')),
            imagePreview: null,
            slugTouched: @json(old('slug') ? true : false),
            quill: null,
            draftKey: 'cms_article_create_draft',
            hasDraft: false,
            saveState: 'idle',
            saveLabel: 'Belum ada perubahan',
            autosaveTimer: null,
            init() {
                this.hasDraft = !!localStorage.getItem(this.draftKey);
            },
            syncSlug() {
                if (this.slugTouched) return;
                this.slug = this.slugify(this.title);
            },
            markDirty() {
                this.saveState = 'idle';
                this.saveLabel = 'Belum tersimpan';
                this.autosaveDraft();
            },
            slugify(value) {
                return (value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },
            previewImage(event) {
                const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                if (!file) {
                    this.imagePreview = null;
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    this.markDirty();
                };
                reader.readAsDataURL(file);
            },
            autosaveDraft() {
                if (!this.quill) return;

                this.saveState = 'saving';
                this.saveLabel = 'Menyimpan draft lokal...';

                const payload = {
                    title: this.title,
                    slug: this.slug,
                    content: this.quill.root.innerHTML,
                    savedAt: new Date().toISOString(),
                };

                localStorage.setItem(this.draftKey, JSON.stringify(payload));
                this.hasDraft = true;

                this.saveState = 'saved';
                this.saveLabel = 'Tersimpan lokal ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },
            restoreDraft() {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;
                try {
                    const data = JSON.parse(raw);
                    this.title = data.title || '';
                    this.slug = data.slug || '';
                    this.slugTouched = (this.slug || '').trim() !== '';
                    if (this.quill && data.content) {
                        this.quill.clipboard.dangerouslyPasteHTML(data.content);
                    }
                    this.saveLabel = 'Draft dipulihkan';
                    this.saveState = 'saved';
                } catch (e) {
                    this.saveLabel = 'Gagal memulihkan draft';
                    this.saveState = 'idle';
                }
            },
            submitForm(publish) {
                if (!this.quill) return;
                document.getElementById('content').value = this.quill.root.innerHTML;
                document.getElementById('is_published_input').value = publish ? '1' : '0';
                if (publish) {
                    localStorage.removeItem(this.draftKey);
                } else {
                    this.autosaveDraft();
                }
                this.$refs.form.submit();
            },
            previewArticle() {
                const content = this.quill ? this.quill.root.innerHTML : '';
                const win = window.open('', '_blank');
                if (!win) return;

                const title = this.title || 'Untitled Article';
                const imageHtml = this.imagePreview ? `<img src="${this.imagePreview}" style="width:100%;max-height:360px;object-fit:cover;border-radius:12px;margin-bottom:16px;">` : '';
                win.document.write(`<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Preview - ${title}</title><style>body{font-family:Arial,sans-serif;background:#111827;color:#f3f4f6;margin:0;padding:24px}main{max-width:900px;margin:0 auto}h1{font-size:32px;margin:0 0 16px}article{line-height:1.7;font-size:16px}a{color:#60a5fa}</style></head><body><main><h1>${title}</h1>${imageHtml}<article>${content}</article></main></body></html>`);
                win.document.close();
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const contentInput = document.getElementById('content');
        const editorEl = document.getElementById('editor');
        if (!contentInput || !editorEl || typeof Quill === 'undefined') return;

        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean']
                ]
            }
        });

        quill.root.style.minHeight = '280px';
        quill.root.style.color = '#f3f4f6';

        const oldContent = contentInput.value || '';
        if (oldContent.trim() !== '') {
            quill.clipboard.dangerouslyPasteHTML(oldContent);
        }

        const root = document.querySelector('[x-data="articleForm()"]');
        if (root && root.__x && root.__x.$data) {
            const component = root.__x.$data;
            component.quill = quill;

            quill.on('text-change', function () {
                component.markDirty();
            });

            if (component.hasDraft && (!contentInput.value || contentInput.value.trim() === '')) {
                component.saveLabel = 'Draft lokal tersedia';
            }

            if (!contentInput.value || contentInput.value.trim() === '') {
                const raw = localStorage.getItem(component.draftKey);
                if (raw) {
                    try {
                        const data = JSON.parse(raw);
                        if (data.content) {
                            quill.clipboard.dangerouslyPasteHTML(data.content);
                        }
                        if (data.title) component.title = data.title;
                        if (data.slug) {
                            component.slug = data.slug;
                            component.slugTouched = true;
                        }
                    } catch (e) {}
                }
            }

            clearInterval(component.autosaveTimer);
            component.autosaveTimer = setInterval(function () {
                if (component.quill) {
                    component.autosaveDraft();
                }
            }, 8000);
        }
    });
</script>
@endpush
@endsection
