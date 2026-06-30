@php
    $translations = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?? []) : []);
    $defaultLocale = $bpLocale;
    $displayValue = $translations[$defaultLocale] ?? reset($translations) ?: null;
@endphp
<span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($displayValue ?? '')), 80) ?: '—' }}</span>
