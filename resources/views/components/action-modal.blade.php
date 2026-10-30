{{--
  Modal form for an action with fields() (A40). Teleported to <body> so no overflow/backdrop
  ancestor can clip it. Submits a plain POST with inputs named input[<field>]; after a validation
  failure the page re-renders with old input + errors and this modal reopens (matched by _bp_action/_bp_row).
--}}
@php
    $scope   = $scope ?? 'entity';
    $rowId   = isset($rowId) ? (string) $rowId : '';
    $modalId = 'bpa-' . $scope . '-' . $rowId . '-' . $action->name;
    $hasInputErrors = collect($errors->getBag('default')->keys())->contains(fn ($k) => str_starts_with((string) $k, 'input.'));
    $reopen = $hasInputErrors
        && old('_bp_action') === $action->name
        && (string) old('_bp_row', '') === $rowId;
    $titleId = $modalId . '-title';
    $confirmJs = '$store.confirm.ask({ title: ' . \Illuminate\Support\Js::from($action->label . '?')
        . ', confirmText: ' . \Illuminate\Support\Js::from($action->label)
        . ', onConfirm: () => $refs.form.submit() })';
@endphp
<div x-data="{ open: {{ $reopen ? 'true' : 'false' }} }"
     @bp-action-open.window="if ($event.detail === {{ \Illuminate\Support\Js::from($modalId) }}) open = true"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4"
             role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>
            <form method="POST" action="{{ $url }}" x-ref="form"
                  class="relative w-full max-w-md rounded-2xl border border-bp-border bg-bp-surface shadow-2xl p-6">
                @csrf
                <input type="hidden" name="_bp_action" value="{{ $action->name }}">
                <input type="hidden" name="_bp_row" value="{{ $rowId }}">
                @if($scope === 'bulk')
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="keys[]" :value="id">
                    </template>
                @endif
                <h3 id="{{ $titleId }}" class="font-display text-lg font-semibold text-bp-gray-900 mb-4">{{ $action->label }}</h3>
                @foreach($action->fields as $actionField)
                    @include('bpadmin::components.field-input', [
                        'field'      => $actionField,
                        'value'      => old('input.' . $actionField->name()),
                        'errors'     => $errors,
                        'definition' => $definition,
                        'namePrefix' => 'input',
                    ])
                @endforeach
                <div class="flex items-center justify-end gap-2 mt-6">
                    <button type="button" @click="open = false"
                            class="bp-btn-ghost inline-flex items-center text-sm font-medium py-2 px-4 rounded-xl">{{ __('bpadmin::common.buttons.cancel') }}</button>
                    <button type="{{ $action->confirm ? 'button' : 'submit' }}"
                            @if($action->confirm) @click="{{ $confirmJs }}" @endif
                            class="bp-btn-primary inline-flex items-center text-sm font-semibold py-2 px-4 rounded-xl">{{ $action->label }}</button>
                </div>
            </form>
        </div>
    </template>
</div>
