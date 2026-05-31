@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])

@php
    $inputName      = $name ?? $field->name();
    $inputErrorKey  = $errorKey ?? $field->name();
    $localeProvider = app(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class);
    $locales        = $localeProvider->availableLocales();
    $defaultLocale  = $localeProvider->defaultLocale();
    $translations   = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?? []) : []);
    $innerType      = method_exists($field, 'innerType') ? $field->innerType() : 'text';
    // Stable, deterministic id so re-renders preserve element identity (Alpine state,
    // editor instances, error bindings) instead of regenerating a new uniqid per request.
    $componentId    = 'translatable-' . preg_replace('/[^a-z0-9_-]/i', '_', $field->name()) . '-' . substr(md5($field->name()), 0, 8);
@endphp

<div x-data="{ activeTab: '{{ $defaultLocale }}' }" id="{{ $componentId }}">
    {{-- Locale tabs --}}
    <div class="flex gap-1 mb-2 border-b border-bp-border">
        @foreach($locales as $locale)
            <button type="button"
                    @click="activeTab = '{{ $locale }}'"
                    :class="activeTab === '{{ $locale }}'
                        ? 'border-bp-primary text-bp-primary'
                        : 'border-transparent text-bp-gray-400 hover:text-bp-gray-600'"
                    class="px-3 py-1.5 text-xs font-medium border-b-2 -mb-px transition-colors uppercase">
                {{ $locale }}
            </button>
        @endforeach
    </div>

    {{-- Input per locale --}}
    @foreach($locales as $locale)
        <div x-show="activeTab === '{{ $locale }}'" x-cloak>
            @if($innerType === 'editor')
                @php $editorId = $componentId . '-editor-' . $locale; @endphp
                <input type="hidden"
                       id="{{ $editorId }}-input"
                       name="{{ $inputName }}[{{ $locale }}]"
                       value="{{ $translations[$locale] ?? '' }}">
                <div id="{{ $editorId }}"
                     class="@error($inputErrorKey . '.' . $locale) ring-1 ring-red-300 @enderror"></div>

                <script>
                (function () {
                    var container = document.getElementById('{{ $editorId }}');
                    var input = document.getElementById('{{ $editorId }}-input');
                    if (!container || typeof Quill === 'undefined') return;

                    var quill = new Quill(container, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ header: [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                [{ align: [] }],
                                ['clean'],
                            ],
                        },
                        placeholder: '{{ $field->label() }} ({{ strtoupper($locale) }})...',
                    });

                    var initial = input.value;
                    if (initial) {
                        quill.clipboard.dangerouslyPasteHTML(initial);
                    }

                    quill.on('text-change', function () {
                        input.value = quill.getText().trim() ? quill.root.innerHTML : '';
                    });
                }());
                </script>
            @else
                <input type="text"
                       id="{{ $inputName }}_{{ $locale }}"
                       name="{{ $inputName }}[{{ $locale }}]"
                       value="{{ old($inputErrorKey . '.' . $locale, $translations[$locale] ?? '') }}"
                       placeholder="{{ $field->label() }} ({{ strtoupper($locale) }})"
                       class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputErrorKey . '.' . $locale) border-red-300 @enderror">
            @endif

            @error($inputErrorKey . '.' . $locale)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>
