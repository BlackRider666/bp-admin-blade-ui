@props(['field', 'record', 'definition' => null])
@php
    $value = $record->get($field->name());

    $bpDisplayRelation = static function (mixed $item, string $displayKey): string {
        if (!is_array($item) && !is_object($item)) {
            return (string) $item;
        }
        $arr = is_object($item) ? (array) $item : $item;
        $raw = $arr[$displayKey] ?? $arr['name'] ?? $arr['title'] ?? null;
        if (is_string($raw) && str_starts_with(ltrim($raw), '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }
        if (is_array($raw)) {
            $locale = app(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class)->defaultLocale();
            $raw = $raw[$locale] ?? reset($raw) ?: null;
        }
        if ($raw === null || $raw === '') {
            return '#' . ($arr['id'] ?? '?');
        }
        return strip_tags((string) $raw);
    };
@endphp

@switch($field->type())
    @case('boolean')
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $value ? 'bg-green-100 text-green-800' : 'bg-bp-gray-100 text-bp-gray-600' }}">
            {{ $value ? 'Yes' : 'No' }}
        </span>
        @break

    @case('image')
        @if($value)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($value) }}" alt="{{ $definition ? bp_field_label($definition, $field) : $field->label() }}" class="h-10 w-10 rounded object-cover">
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('file')
        @if($value)
            <a href="{{ \BlackParadise\LaravelAdmin\Support\BPAdminFileUrl::signed('public', $value) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-xs">
                {{ basename($value) }}
            </a>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('morph_file')
        @php
            // Normalisation shared with morph-file.blade.php via bp_morph_file_meta().
            ['path' => $mfPath, 'name' => $mfName, 'isImage' => $mfIsImage]
                = bp_morph_file_meta($value);
        @endphp
        @if($mfPath)
            @if($mfIsImage)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($mfPath) }}"
                     alt="{{ $mfName ?? basename($mfPath) }}"
                     class="h-10 w-10 rounded object-cover">
            @else
                <a href="{{ \BlackParadise\LaravelAdmin\Support\BPAdminFileUrl::signed('public', $mfPath) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="text-bp-primary hover:text-bp-primary-hover underline underline-offset-4 text-xs font-mono-bp">
                    {{ $mfName ?? basename($mfPath) }}
                </a>
            @endif
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('editor')
        <span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($value ?? '')), 80) }}</span>
        @break

    @case('date')
        @if($value)
            @php $dt = \Carbon\Carbon::parse($value); @endphp
            <span class="text-bp-gray-600 text-xs tabular-nums" title="{{ $dt->format('D, d M Y') }}">{{ $dt->format('d.m.Y') }}</span>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('datetime')
        @if($value)
            @php $dt = \Carbon\Carbon::parse($value); @endphp
            <span x-data="bpRelativeTime({{ \Illuminate\Support\Js::from($dt->toIso8601String()) }})"
                  class="text-bp-gray-600 text-xs tabular-nums inline-flex items-center gap-1.5"
                  title="{{ $dt->format('D, d M Y H:i:s') }}">
                <svg class="h-3 w-3 text-bp-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="label">{{ $dt->format('d.m.Y H:i') }}</span>
            </span>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('email')
        @if($value)
            <a href="mailto:{{ $value }}" class="text-blue-600 hover:underline">{{ $value }}</a>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('hashed')
        <span class="text-bp-gray-400 text-xs italic">&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;</span>
        @break

    @case('belongs_to')
        @php
            $relationName = $field->relationName();
            $displayField = $field->displayField();
            $relation = $record->relation($relationName);
            // Use the shared resolver so a translatable display field
            // (e.g. City->name = ['en'=>…, 'uk'=>…]) renders as a locale
            // string instead of reaching {{ }} as a raw array.
            $displayValue = $relation !== null
                ? $bpDisplayRelation($relation, $displayField)
                : (string) ($value ?? '');
        @endphp
        {{ $displayValue }}
        @break

    @case('belongs_to_many')
        @php
            $relItems = $record->relation($field->relationName()) ?? [];
            if (!is_array($relItems)) { $relItems = []; }
            $displayKey = $field->displayField();
        @endphp
        @if(!empty($relItems))
            <div class="flex flex-wrap gap-1">
                @foreach($relItems as $item)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-bp-primary/10 text-bp-primary">
                        {{ $bpDisplayRelation($item, $displayKey) }}
                    </span>
                @endforeach
            </div>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('has_many')
    @case('morph_many')
        @php
            $relItems = $record->relation($field->relationName()) ?? [];
            if (!is_array($relItems)) { $relItems = []; }
            $displayKey = $field->displayField();
        @endphp
        @if(!empty($relItems))
            <ul class="flex flex-col gap-0.5 text-sm text-bp-gray-700">
                @foreach($relItems as $item)
                    <li class="truncate">{{ $bpDisplayRelation($item, $displayKey) }}</li>
                @endforeach
            </ul>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('morph_to')
        @php
            $relItem = $record->relation($field->relationName());
            $displayKey = $field->displayField();
        @endphp
        @if(!empty($relItem))
            <span class="text-bp-gray-700">{{ $bpDisplayRelation($relItem, $displayKey) }}</span>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('enum')
        @php
            $enumOptions = $field->meta()['options'] ?? [];
            $isMulti     = (bool) ($field->meta()['multiple'] ?? false);
        @endphp
        @if($isMulti && is_array($value) && count($value))
            <span class="inline-flex flex-wrap gap-1">
                @foreach($value as $item)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bp-gray-100 text-bp-gray-700">
                        {{ $enumOptions[$item] ?? $item }}
                    </span>
                @endforeach
            </span>
        @elseif(!$isMulti && $value !== null && $value !== '')
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bp-gray-100 text-bp-gray-700">
                {{ $enumOptions[$value] ?? $value }}
            </span>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('relation_path')
        @php
            $resolved = method_exists($record, 'getByPath')
                ? $record->getByPath($field->path())
                : null;
            $displayValue = is_array($resolved) || is_object($resolved)
                ? null  // не знаємо який атрибут — показуємо dash
                : $resolved;
        @endphp
        @if($displayValue !== null && $displayValue !== '')
            <span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit((string) $displayValue, 80) }}</span>
        @else
            <span class="text-bp-gray-400 text-xs">—</span>
        @endif
        @break

    @case('translatable')
        @php
            $translations = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?? []) : []);
            $defaultLocale = app(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class)->defaultLocale();
            $displayValue = $translations[$defaultLocale] ?? reset($translations) ?: null;
        @endphp
        <span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($displayValue ?? '')), 80) ?: '—' }}</span>
        @break

    @default
        <span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit((string) ($value ?? ''), 80) }}</span>
@endswitch
