<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    public function createSession(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = \App\Models\Product::findOrFail($data['product_id']);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => (int)($product->price * 100),
                ],
                'quantity' => $data['quantity'],
            ]],
            'mode' => 'payment',
            'success_url' => $request->get('success_url') ?? config('app.url') . '/dashboard?paid=1',
            'cancel_url' => $request->get('cancel_url') ?? config('app.url') . '/dashboard?paid=0',
        ]);

        return response()->json(['id' => $session->id]);
    }
}
