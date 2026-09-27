<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderNotification;
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
    public function placeOrder(Request $request)
    {
        $cart = session()->get('cart', []); // or Cart::where('user_id', auth()->id())->get();

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'phone'     => ['required', 'string', 'max:30'],
            'address'   => ['required', 'string', 'max:255'],
            'city'      => ['required', 'string', 'max:255'],
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
                'payment_method' => 'Flutterwave',
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

            // Clear cart
            session()->forget('cart'); // or delete Cart::where(...)

            // Notifications are best-effort: a mail/queue hiccup here
            // should never undo an already-committed order.
            try {
                if ($customerId) {
                    $user = User::find($customerId);
                    $user?->notify(new NewOrderNotification($order));
                } else {
                    Notification::route('mail', $order->guest_email)
                        ->notify(new NewOrderNotification($order));
                }

                $admins = User::where('user_role', '!=', '1')->get(); // Adjust based on your role system
                foreach ($admins as $admin) {
                    $admin->notify(new NewOrderNotification($order));
                }
            } catch (\Exception $notifyException) {
                report($notifyException);
            }

            return redirect()->back()->with(
                'success',
                "Order placed successfully! Your order number is {$order->order_number}. " .
                'Keep it (with the email you used) to track your order.'
            );

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Order failed: ' . $e->getMessage());
        }
    }

    /**
     * Public "track my order" form — no account required.
     */
    public function trackOrderForm(Request $request)
    {
        $order = null;

        if ($request->filled('order_number') && $request->filled('email')) {
            $order = $this->findTrackableOrder($request->input('order_number'), $request->input('email'));

            if (! $order) {
                return view('Ecommerce.track-order', ['order' => null])
                    ->with('error', 'We could not find an order with that order number and email combination.');
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
            'email'        => ['required', 'email'],
        ]);

        return redirect()->route('order.track', [
            'order_number' => $request->input('order_number'),
            'email'        => $request->input('email'),
        ]);
    }

    /**
     * Look an order up by order number + email, matching either a
     * guest checkout or an order placed by a logged-in customer, so
     * the same tracking form works for both.
     */
    protected function findTrackableOrder(string $orderNumber, string $email)
    {
        return Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->where(function ($query) use ($email) {
                $query->where('guest_email', $email)
                    ->orWhereHas('user', function ($userQuery) use ($email) {
                        $userQuery->where('email', $email);
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
        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated!');
    }

    public function showOrderinformation($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);

        return view('Admin.order-information', compact('order'));
    }
}