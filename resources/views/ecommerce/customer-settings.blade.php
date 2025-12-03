@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/2fa::2fa.name'))

@section('content')
    <div class="bb-customer-card-list">
        @include('plugins/2fa::ecommerce.customer-2fa-card')
    </div>
@endsection
