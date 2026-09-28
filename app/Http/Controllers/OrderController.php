<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\MarzPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    /**
     * Place an order for either a logged-in customer or a guest.
     *
     * A guest never has an account, so we can't look shipping/contact
     * details up from a `users` row the way a logged-in checkout does.
     * Instead we collect the standard set of guest-checkout details
     * directly from the form (name, email, phone, address, city,
     * country) and store them on the order itself, together with a
     * unique order number, so the order can still be looked up and
     * tracked later purely from what the customer submitted.
     */
    public function placeOrder(Request $request, MarzPayService $marz)
    {
        $cart = session()->get('cart', []); // or Cart::where('user_id', auth()->id())->get();

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        // Numbers must be in international format (+256...). Tidy spaces
        // and dashes first so "+256 772-123 456" is accepted.
        $request->merge([
            'phone'         => $marz->cleanPhone($request->input('phone')),
            'payment_phone' => $marz->cleanPhone($request->input('payment_phone')),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['required', 'string', 'regex:' . MarzPayService::PHONE_REGEX],
            'address'   => ['required', 'string', 'max:255'],
            'city'      => ['required', 'string', 'max:255'],
            'payment_phone' => ['required', 'string', 'regex:' . MarzPayService::PHONE_REGEX],
        ], [
            'phone.regex'         => 'Enter your mobile number with the country code, e.g. +256772123456.',
            'payment_phone.regex' => 'Enter your mobile money number with the country code, e.g. +256772123456.',
        ]);

        $customerId = session('LoggedCustomer'); // null for a guest
        $fullName   = $validated['full_name'];

        DB::beginTransaction();

        try {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Create Order
            $order = Order::create([
                'order_number'   => Order::generateOrderNumber(),
                'user_id'        => $customerId, // null for guest
                'guest_name'     => $fullName,
                'guest_email'    => $validated['email'],
                'guest_phone'    => $validated['phone'],
                'total_amount'   => $total,
                'status'         => 'pending',
                'payment_method' => 'Mobile Money (Marz Pay)',
                'payment_status' => 'unpaid',
                'shipping_info'  => [
                    'name'    => $fullName,
                    'phone'   => $validated['phone'],
                    'email'   => $validated['email'],
                    'address' => $validated['address'],
                    'city'    => $validated['city'],
                    'country' => 'Uganda',
                ],
            ]);

            // Add Items
            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            DB::commit();

            // Clear cart (the order now exists; paying is tracked on the order)
            session()->forget('cart'); // or delete Cart::where(...)
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Order failed: ' . $e->getMessage());
        }

        // The order only counts as done once it is paid: send the mobile
        // money prompt to the customer's phone, then show the payment page
        // (which waits for approval). Customer + admin emails go out when
        // the payment is confirmed — see MarzPayService::markSuccessful().
        try {
            $marz->startPayment($order, $validated['payment_phone']);
        } catch (\Throwable $e) {
            // The order is saved; the payment page offers "Pay Now" to retry.
            report($e);
        }

        return redirect($order->paymentUrl());
    }

    /**
     * Public "track my order" form — no account required.
     */
    public function trackOrderForm(Request $request)
    {
        $order = null;

        if ($request->filled('order_number') && $request->filled('phone')) {
            $order = $this->findTrackableOrder($request->input('order_number'), $request->input('phone'));

            if (! $order) {
                return view('Ecommerce.track-order', ['order' => null])
                    ->with('error', 'We could not find an order with that order number and phone number combination.');
            }
        }

        return view('Ecommerce.track-order', compact('order'));
    }

    /**
     * Handle the "track my order" form submission.
     */
    public function trackOrder(Request $request)
    {
        $request->validate([
            'order_number' => ['required', 'string'],
            'phone'        => ['required', 'string'],
        ]);

        return redirect()->route('order.track', [
            'order_number' => $request->input('order_number'),
            'phone'        => $request->input('phone'),
        ]);
    }

    /**
     * Look an order up by order number + email, matching either a
     * guest checkout or an order placed by a logged-in customer, so
     * the same tracking form works for both.
     */
    protected function findTrackableOrder(string $orderNumber, string $phone)
    {
        // Match however the number was typed/stored: as entered, +256...
        // or the local 0... form (older orders were saved without a code).
        $marz     = app(MarzPayService::class);
        $intl     = $marz->normalizePhone($phone);
        $variants = array_values(array_unique(array_filter([
            trim($phone),
            $marz->cleanPhone($phone),
            $intl,
            str_starts_with($intl, '+256') ? '0' . substr($intl, 4) : null,
        ])));

        return Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->where(function ($query) use ($variants) {
                $query->whereIn('guest_phone', $variants)
                    ->orWhereHas('user', function ($userQuery) use ($variants) {
                        $userQuery->whereIn('mobile', $variants);
                    });
            })
            ->first();
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', session('LoggedCustomer'))->latest()->get();

        return view('customer.orders', compact('orders'));
    }

    public function showOrders($id)
    {
        $order = Order::with('items.product')->where('user_id', session('LoggedCustomer'))->findOrFail($id);

        return view('customer.all-orders', compact('order'));
    }

    public function adminOrders()
    {
        $orders = Order::with('user')->latest()->paginate(15);

        return view('Admin.admin-orders', compact('orders'));
    }

    public function adminOrdersNotifications()
    {

        $notifications = DB::table('notifications')->latest()->get();
        return view('Admin.admin-orders', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,canceled',
        ]);

        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated!');
    }

    public function destroy(Order $order)
    {
        $number = $order->order_number ?? '#' . $order->id;

        try {
            $order->delete(); // soft delete — see SoftDeletes on the Order model
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('admin.orders')->with('error', "Could not delete order {$number}.");
        }

        return redirect()->route('admin.orders')->with('success', "Order {$number} deleted.");
    }

    public function showOrderinformation($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);

        return view('Admin.order-information', compact('order'));
    }
}