@extends('base::components.dashboard.layouts.master')

@section('title', __($section['label']))

@section('content')
    <x-dashboard.page-header
        :title="__($section['label'])"
        :breadcrumbs="[['name' => __('dashboard.structures')], ['name' => __($section['label'])]]"
    />

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('structure::dashboard.partials.section-page')
@endsection

@section('page-script')
    @include('structure::dashboard.partials.section-scripts')
@endsection

{{-- Rich-text editor for every Structure textarea. Quill ships in the Vite
     manifest already, so this needs no rebuild. --}}
@push('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/editor.scss'])
@endpush

@push('page-style')
    <style>
        .rich-editor .ql-editor {
            min-height: 160px;
        }
    </style>
@endpush

@push('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/quill.js'])
@endpush

@push('page-script')
    <script>
        (function () {
            function initRichEditors() {
                if (typeof window.Quill === 'undefined') {
                    return;
                }

                document.querySelectorAll('textarea[data-rich-editor]').forEach(function (textarea) {
                    if (textarea.dataset.richEditorReady) {
                        return;
                    }
                    textarea.dataset.richEditorReady = '1';
                    textarea.classList.add('d-none');

                    var container = document.createElement('div');
                    container.className = 'rich-editor';
                    textarea.parentNode.insertBefore(container, textarea.nextSibling);

                    var editor = new window.Quill(container, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ header: [2, 3, false] }],
                                ['bold', 'italic', 'underline'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                ['link'],
                                ['clean'],
                            ],
                        },
                    });

                    editor.root.innerHTML = textarea.value;
                    editor.on('text-change', function () {
                        textarea.value = editor.root.innerHTML;
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', initRichEditors);
            window.addEventListener('load', initRichEditors);
        })();
    </script>
@endpush
