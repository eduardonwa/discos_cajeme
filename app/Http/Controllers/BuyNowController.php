<?php

namespace App\Http\Controllers;

use App\Actions\Webshop\CreateStripeCheckoutSession;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class BuyNowController extends Controller
{
    public function __invoke(
        Request $request,
        CreateStripeCheckoutSession $action
    ) {
        $request->validate([
            'variant_id' => ['required', 'integer'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        return $action->createBuyNow(
            productVariant: $variant,
            qty:            $request->integer('qty', 1),
            user:           $request->user(),
            coupon:         null
        );
    }
}
