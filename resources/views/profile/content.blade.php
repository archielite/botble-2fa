@push('header')
    <script>
        window.trans = {{ Js::from(['trans' => trans('plugins/2fa::2fa')]) }};
    </script>
@endpush

<x-core::tab.pane id="twofa">
    @if(! ArchiElite\TwoFactorAuthentication\TwoFactor::userHasEnabled(auth()->user()))
        <x-core::button color="primary" onclick="vueApp.eventBus.$emit('show-two-factor-setup-modal')" icon="ti ti-lock">
            {{ trans('plugins/2fa::2fa.ask_enable_button') }}
        </x-core::button>

        <two-factor-setup-modal></two-factor-setup-modal>
    @else
        <x-core::button color="warning" onclick="vueApp.eventBus.$emit('show-two-factor-remove-modal')" icon="ti ti-lock-off">
            {{ trans('plugins/2fa::2fa.ask_disable_button') }}
        </x-core::button>

        <two-factor-remove-modal></two-factor-remove-modal>
    @endif
</x-core::tab.pane>
