@extends('layouts.main')

@section('head')
    <style>
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }
        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }
        .StripeElement--invalid {
            border-color: #fa755a;
        }
        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }
        .error-while-pay {
            position: relative;
            right: -3%;
            color: red;
            margin-top: 8px;
        }
        .card-header {
            color: #388E3C;
            font-size: 15px;
            font-weight: bold;
        }
        .card-details-txt {
            color: #388E3C;
            /* font-size: 16px; */
        }
        .fa-sync {
            color: #388E3C;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div id="success" style="display: none" class="col-md-8 text-center h3 p-4 bg-success text-light rounded">Your purchase was completed successfully!</div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Credit Card details</div>
                    <span class="error-while-pay" id="errorWhilepay"></span>
                    <form method="POST" action="{{ route('products.purchase') }}" class="card-form mt-3 mb-3 mx-4">
                        <p class="card-details-txt">Please provide valid credit card details including cardholder name, card number, expiration date, and CVV code.</p>
                        @csrf
                        <input type="hidden" name="payment_method" class="payment-method">
                        <input class="StripeElement mb-3" name="card_holder_name" placeholder="Card holder name">
                        <div>
                            <div id="card-element"></div>
                        </div>
                        <div id="card-errors" role="alert"></div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn bg-cart pay">
                                Submit Order {{$total}}$ <span class="icon" hidden><i class="fas fa-sync fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
<script src="https://js.stripe.com/v3/"></script>
<script>
    let stripe = Stripe("{{ env('STRIPE_KEY') }}")
    let elements = stripe.elements()
    let style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    }
    let card = elements.create('card', {style: style})
    card.mount('#card-element')
    let paymentMethod = null
    $('.card-form').on('submit', function (e) {
        $('button.pay').attr('disabled', true)
        if (paymentMethod) {
            return true
        }
        stripe.confirmCardSetup(
            "{{ $intent->client_secret }}",
            {
                payment_method: {
                    card: card,
                    billing_details: {name: $('.card_holder_name').val()}
                }
            }
        ).then(function (result) {
            if (result.error) {
                document.getElementById("errorWhilepay").innerHTML = "Invalid or empty fields are not allowed";
                $('button.pay').removeAttr('disabled')
            } else {
                paymentMethod = result.setupIntent.payment_method
                $('.payment-method').val(paymentMethod)
                $('.card-form').submit()
                $('span.icon').removeAttr('hidden');
                $('button.pay').attr('disabled', true)
            }
        })
        return false
    })
</script>
@endsection
