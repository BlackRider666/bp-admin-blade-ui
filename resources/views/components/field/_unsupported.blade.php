@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
{{--
    Fallback rendered when a field type has no dedicated form template.
    Displays a read-only informational notice so the form does not crash with
    a ViewNotFound 500 error.
--}}
<div class="rounded-xl border border-bp-border-soft/60 bg-bp-surface-2/40 px-4 py-3 text-xs text-bp-muted flex items-start gap-2">
    <svg class="h-3.5 w-3.5 mt-0.5 flex-shrink-0 text-bp-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>
        Field type <code class="font-mono-bp text-[11px] bg-bp-gray-100 px-1 rounded">{{ $field->type() }}</code>
        is not supported on the form. This field is managed outside the admin panel.
    </span>
</div>
