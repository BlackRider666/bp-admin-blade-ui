@php
    $flash = [];
    if (session('success')) $flash[] = ['kind' => 'success', 'msg' => session('success')];
    if (session('warning')) $flash[] = ['kind' => 'warning', 'msg' => session('warning')];
    if (session('error'))   $flash[] = ['kind' => 'error',   'msg' => session('error')];
    foreach ($errors->all() as $err) $flash[] = ['kind' => 'error', 'msg' => $err];
@endphp

<div class="fixed bottom-6 right-6 z-40 flex flex-col gap-2 w-80 pointer-events-none"
     x-init="@foreach($flash as $item) $store.ui.toast({{ \Illuminate\Support\Js::from($item['msg']) }}, '{{ $item['kind'] }}', {{ $item['kind'] === 'error' ? 7000 : 4500 }}); @endforeach">
    <template x-for="t in $store.ui.toasts" :key="t.id">
        <div class="bp-toast pointer-events-auto rounded-xl border backdrop-blur px-4 py-3 text-sm flex items-start gap-3"
             :class="{
                'border-emerald-500/30 bg-emerald-500/10 text-emerald-200': t.kind === 'success',
                'border-amber-500/30 bg-amber-500/10 text-amber-200':       t.kind === 'warning',
                'border-red-500/30 bg-red-500/10 text-red-200':             t.kind === 'error',
                'border-bp-border-soft bg-bp-surface-2/90 text-bp-gray-800': t.kind === 'info'
             }">
            <span class="h-5 w-5 flex items-center justify-center flex-shrink-0 mt-0.5">
                <template x-if="t.kind === 'success'">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="t.kind === 'warning'">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <template x-if="t.kind === 'error'">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="t.kind === 'info'">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </span>
            <span class="flex-1" x-text="t.msg"></span>
            <button @click="$store.ui.dismissToast(t.id)" class="text-current opacity-60 hover:opacity-100 transition-opacity text-lg leading-none">&times;</button>
        </div>
    </template>
</div>
