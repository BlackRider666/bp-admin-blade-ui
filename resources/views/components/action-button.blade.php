{{--
  Trigger for one entity action (A40).
  Props: $action (ActionViewModel), $scope ('entity'|'bulk'|'row'), $url (POST target),
         $rowId (row scope), $variant ('button'|'menu'|'icon-less compact' via 'button'/'menu').
  Plain actions are a plain POST form (CSRF, no fetch); actions with fields() open <x-action-modal>.
--}}
@php
    $scope   = $scope ?? 'entity';
    $rowId   = isset($rowId) ? (string) $rowId : '';
    $variant = $variant ?? 'button';
    $modalId = 'bpa-' . $scope . '-' . $rowId . '-' . $action->name;
    $btnClass = $variant === 'menu'
        ? 'block w-full text-left px-3 py-2 text-sm text-bp-gray-700 hover:bg-white/5 transition-colors'
        : 'bp-btn-ghost inline-flex items-center gap-1.5 text-sm font-medium py-2 px-3 rounded-xl';
    $confirmJs = '$store.confirm.ask({ title: ' . \Illuminate\Support\Js::from($action->label . '?')
        . ', confirmText: ' . \Illuminate\Support\Js::from($action->label)
        . ', onConfirm: () => $el.closest(\'form\').submit() })';
@endphp
@if($action->hasForm)
    <button type="button"
            @if($variant === 'menu') role="menuitem" @endif
            @click="$dispatch('bp-action-open', {{ \Illuminate\Support\Js::from($modalId) }})"
            class="{{ $btnClass }}">{{ $action->label }}</button>
@else
    <form method="POST" action="{{ $url }}" class="{{ $variant === 'menu' ? 'block' : 'inline-flex' }}">
        @csrf
        @if($scope === 'bulk')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="keys[]" :value="id">
            </template>
        @endif
        <button type="{{ $action->confirm ? 'button' : 'submit' }}"
                @if($variant === 'menu') role="menuitem" @endif
                @if($action->confirm) @click="{{ $confirmJs }}" @endif
                class="{{ $btnClass }}">{{ $action->label }}</button>
    </form>
@endif
