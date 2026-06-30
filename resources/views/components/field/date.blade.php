@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    // Bug #13: $value may be a rejected old() input (e.g. "not-a-date") on a
    // re-rendered form. DateFormatter::format guards Carbon::parse → never throws.
    // Fall back to '' — a <input type=date> can only display a Y-m-d string.
    $dateValue = \BlackParadise\LaravelAdminBladeUI\Support\DateFormatter::format($value, 'Y-m-d') ?? '';
@endphp

<input type="date"
       id="{{ $inputName }}"
       name="{{ $inputName }}"
       value="{{ $dateValue }}"
       class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputErrorKey) border-red-300 @enderror">
