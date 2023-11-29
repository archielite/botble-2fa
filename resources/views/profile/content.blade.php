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

        <two-factor-setup-modal
            qr-code-url="{{ route('two-factor.system.users.qr-code') }}"
            confirm-url="{{ route('two-factor.system.users.confirm') }}"
            recovery-codes-url="{{ route('two-factor.system.users.recovery-codes') }}"
            enable-url="{{ route('two-factor.system.users.enable') }}"
        ></two-factor-setup-modal>
    @else
        <x-core::button color="warning" onclick="vueApp.eventBus.$emit('show-two-factor-remove-modal')" icon="ti ti-lock-off">
            {{ trans('plugins/2fa::2fa.ask_disable_button') }}
        </x-core::button>

        <two-factor-remove-modal disable-url="{{ route('two-factor.system.users.disable') }}"></two-factor-remove-modal>
    @endif
</x-core::tab.pane>
