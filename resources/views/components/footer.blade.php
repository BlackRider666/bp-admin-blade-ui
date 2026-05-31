@php $copyright = config('bpadmin.copyright'); @endphp
@if($copyright)
<footer class="border-t border-bp-border bg-bp-surface/60 backdrop-blur px-6 py-3 flex items-center justify-between flex-shrink-0">
    <p class="text-[11px] uppercase tracking-widest text-bp-muted">{{ $copyright }}</p>
    <p class="text-[11px] uppercase tracking-widest text-bp-muted flex items-center gap-2">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.9)]"></span>
        v{{ config('bpadmin.version', '3.0') }}
    </p>
</footer>
@endif
