@extends('core/acl::auth.master')

@push('header')
    <script>
        window.trans = {{ Js::from(['trans' => trans('plugins/2fa::2fa')]) }};
    </script>
@endpush

@section('content')
    <two-factor-challenge url="{{ route('two-factor.challenge') }}"></two-factor-challenge>
@endsection
