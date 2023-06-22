@push('header')
    <script>
        window.trans = {{ Js::from(['trans' => trans('plugins/2fa::2fa')]) }};
    </script>
@endpush

<div class="tab-pane" id="twofa">
    @if(! ArchiElite\TwoFactorAuthentication\TwoFactor::userHasEnabled(auth()->user()))
        <button type="button" class="btn btn-info" onclick="vueApp.eventBus.$emit('show-two-factor-setup-modal')">
            {{ trans('plugins/2fa::2fa.ask_enable_button') }}
        </button>

        <two-factor-setup-modal></two-factor-setup-modal>
    @else
        <button type="button" class="btn btn-danger" onclick="vueApp.eventBus.$emit('show-two-factor-remove-modal')">
            {{ trans('plugins/2fa::2fa.ask_disable_button') }}
        </button>

        <two-factor-remove-modal></two-factor-remove-modal>
    @endif
</div>
