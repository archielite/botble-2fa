<div class="tab-pane" id="twofa">
    @if(! Botble\TwoFa\TwoFa::userHasEnabled(auth()->user()))
        <button type="button" class="btn btn-info" onclick="vueApp.eventBus.$emit('show-two-factor-setup-modal')">{{ __('Enable Two-factor Authentication?') }}</button>

        <two-factor-authentication-setup />
    @else
        <button type="button" class="btn btn-danger">{{ __('Disable Two-factor Authentication?') }}</button>
        <two-factor-authentication-remove />
    @endif
</div>


