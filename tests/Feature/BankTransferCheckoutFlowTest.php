<?php

use App\Models\Order;
use App\Models\PaymentSubmission;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeBankTransferProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Bank Transfer Test Product',
        'slug' => 'bank-transfer-test-' . uniqid(),
        'description' => 'Test product',
        'price' => 20000,
        'currency' => 'NGN',
        'status' => 'active',
    ], $attributes));
}

test('starting checkout and opening bank transfer do not create an order', function () {
    $product = makeBankTransferProduct();

    $this->post(route('checkout.create'), [
        'product_id' => $product->id,
    ])->assertRedirect(route('checkout.show', ['product' => $product->id]));

    expect(Order::count())->toBe(0);

    $this->get(route('checkout.bank-transfer', ['product' => $product->id]))
        ->assertOk()
        ->assertSee('Submit Payment Proof')
        ->assertSee('Your order will only be placed when you click Submit Payment Proof');

    expect(Order::count())->toBe(0);
});

test('submitting bank transfer proof creates the order and payment submission', function () {
    Storage::fake('public');

    $product = makeBankTransferProduct();

    $response = $this->post(route('checkout.bank-transfer.submit', ['product' => $product->id]), [
        'customer_name' => 'Ada Okafor',
        'customer_email' => 'ada@example.com',
        'customer_phone' => '08012345678',
        'amount' => '20000.00',
        'bank_name' => 'Access Bank',
        'transaction_reference' => 'TRX-123456',
        'transfer_date' => now()->toDateString(),
        'proof' => UploadedFile::fake()->image('payment-proof.jpg'),
    ]);

    $response->assertRedirect(route('checkout.bank-transfer', ['product' => $product->id]));

    expect(Order::count())->toBe(1);
    expect(PaymentSubmission::count())->toBe(1);

    $order = Order::first();

    expect($order->status)->toBe('pending');
    expect($order->payment_method)->toBe('bank_transfer');
    expect($order->total)->toBe('20000.00');
    expect($order->paymentSubmissions()->first()->status)->toBe('pending');
});
