<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductsController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::controller(MasterController::class)->group(function () {

    Route::group(['middleware' => ['AdminOrCustomerAuth']], function () {

        Route::get('/item-shop', 'itemShop')->name('item.shop');
        Route::get('/item-cart', 'itemCart')->name('item.cart');
        Route::get('/item-details', 'itemDetails')->name('item.details');
        Route::get('/item-checkout', 'itemCheckout')->name('item.checkout');
        Route::get('/contact-us', 'contactUs')->name('contact.us');

        Route::get('/product-item/{id}', 'productItem')->name('product.item');
        Route::get('/user-login', 'userLogin')->name('user.login');
        Route::get('/user-register', 'userRegister')->name('user.register');
        Route::get('/user-profile', 'userProfile')->name('user.profile');

    });

    // Part of checkout, so a guest must be able to reach it too.
    Route::post('/calculate-shipping', 'calculateShippingRate')->name('calculate.shipping');

    Route::post('user-store-new-password', 'store_new_password')->name('user-store-new-password');
    Route::post('/store-user-information', 'storeUserInformation')->name('store.user.information');
    Route::post('/user-login-credentials', 'userloginCredentials')->name('user.login.credentials');
    Route::post('/update-user-profile', 'updateProfile')->name('profile.update');
    Route::post('user-generate-forgot-password-link', 'generateForgotPasswordLink')->name('user-generate-forgot-password-link');

    Route::get('/', 'home')->name('home');
    Route::get('/load-more-products/{type}', 'loadMoreProducts');
    Route::get('/item-categories/{id}', 'itemCategories')->name('item.categories');
    Route::get('/product-options/{id}', 'itemOptions')->name('product.options');
    Route::get('password/reset/{id}', 'createNewPassword')->name('password/reset');
    Route::get('/user-forgot-password', 'userForgotPassword')->name('user.forgot.password');

});

Route::controller(CustomerController::class)->group(function () {

    Route::group(['prefix' => '/customer'], function () {

        Route::group(['middleware' => ['CustomerAuth']], function () {
            Route::get('/dashboard', 'customerDashboard')->name('customer.dashboard');
            Route::get('/contact/{id}/edit', 'edit')->name('contact.edit');
            Route::put('/contact/{id}', 'update')->name('contact.update');
        });

        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::get('/all-customers', 'allCustomers')->name('all.customers');
            Route::get('/admin/customers/{id}', 'showCustomer')->name('admin.customers.show');
            Route::get('/contact-us-messages', 'customerContactUsMessage')->name('customer.contactus.messages');
            Route::get('/contact-us-message-details/{id}', 'showMessageDetails')->name('contactus.messages.details');
        });

        Route::post('/admin/messages/{id}/update-status', 'updateStatus')->name('admin.messages.updateStatus');
        Route::post('/admin/customers/{id}/status', 'updateCustomerStatus')->name('admin.customers.updateStatus');

    });

    Route::post('/contact', 'store')->name('contact.store');
    Route::post('/subscribe', 'subscribe')->name('newsletter.subscribe');
    Route::get('/customer-logout', 'customerLogout')->name('customer.logout');
});

Route::controller(OrderController::class)->group(function () {

    // Checkout works for a guest as well as a logged-in customer: when a
    // `LoggedCustomer` session exists the order is linked to that account,
    // otherwise it's stored with the guest details submitted on the form.
    Route::post('/place-order', 'placeOrder')->name('order.place');

    // Public order tracking — a guest (or a customer who'd rather not log
    // in) can look an order up with just its order number and the email
    // it was placed with.
    Route::get('/track-order', 'trackOrderForm')->name('order.track');
    Route::post('/track-order', 'trackOrder')->name('order.track.submit');

    Route::group(['middleware' => ['CustomerAuth']], function () {
        Route::group(['prefix' => '/customer'], function () {
            Route::get('/orders', 'myOrders')->name('customer.orders');
            Route::get('/my-orders/{order}', 'showOrders')->name('customer.order.view');
        });
    });

    Route::group(['middleware' => ['AdminAuth']], function () {
        Route::group(['prefix' => '/shanana'], function () {
            Route::get('/orders/{order}', 'showOrderinformation')->name('admin.orders.show');
            Route::get('/orders-notifications', 'adminOrdersNotifications')->name('admin.order.notifications');
            Route::get('/admin-orders', 'adminOrders')->name('admin.orders');
            Route::get('/orders/{order}/invoice', 'showInvoice')->name('orders.invoice');
            Route::post('/orders/{order}/status', 'updateStatus')->name('admin.orders.updateStatus');
            Route::delete('/orders/{order}', 'destroy')->name('admin.orders.destroy');
        });
    });

});

Route::controller(AdminController::class)->group(function () {

    Route::group(['prefix' => '/shanana'], function () {
        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::get('/dashboard', 'adminDashboard')->name('admin.dashboard');
        });
    });
    Route::get('/admin-logout', 'adminLogout')->name('admin.logout');
    Route::get('/clear-session', 'flushSession');
});

Route::controller(ProductsController::class)->group(function () {

    Route::group(['middleware' => ['AdminAuth']], function () {

        Route::group(['prefix' => '/shanana'], function () {
            Route::get('/product-categories', 'productCategories')->name('product.categories');
            Route::get('/add-category', 'addCategory')->name('add.category');
            Route::get('/all-products', 'allProducts')->name('all.products');
            Route::get('/add-product', 'addProduct')->name('add.product');
            Route::get('/edit-product/{id}/', 'editProduct')->name('edit.product');
            Route::get('/edit-category/{id}/', 'editCategory')->name('edit.category');

            Route::get('/delete-categories/{id}/', 'deleteCategories')->name('delete.categories');
            Route::get('/edit-categories/{id}/', 'editCategories')->name('edit.categories');
        });

        // Route::post('/update-category/{id}', 'updateCategory')->name('categories.update');
        Route::put('/update-category/{id}', 'updateCategory')->name('categories.update');
        Route::post('/store-category', 'storeCategory')->name('store.category');
        Route::post('/store-product', 'storeProduct')->name('store.product');
        Route::post('/store-review', 'storeReview')->name('store.review');

        Route::put('/products/{id}', 'updateProduct')->name('products.update');
        Route::delete('/products/{product}', 'deleteProduct')->name('products.destroy');
        Route::delete('/reviews/{id}', 'deleteReview')->name('reviews.destroy');
        Route::delete('/categories/{category}', 'deleteCategory')->name('category.destroy');

    });

    // Cart actions never require an account — a guest must be able to
    // add/remove/update items in their own session-backed cart before
    // (and without ever) creating one.
    Route::post('/shop/add-to-cart/{id}', 'addToCart')->name('shop.add.cart');
    Route::get('/shop/cart/remove/{id}', 'removeFromCart')->name('shop.cart.remove');
    Route::post('/cart/update-quantity', 'updateQuantity')->name('shop.cart.updateQuantity');
    Route::get('/exchange-rates', 'getExchangeRates')->name('exchange.rates');

    Route::group(['middleware' => ['CustomerAuth']], function () {
        Route::post('/customer-store-review', 'customerStoreReview')->name('customer.store.review');
    });

});

Route::get('/search-products', function (\Illuminate\Http\Request $request) {
    $query = $request->input('query');
    $products = Product::where('product_name', 'like', "%{$query}%")
        ->select('id', 'product_name', 'price', 'sale_price', 'featured_image_1')
        ->take(10)
        ->get();

    return response()->json($products);
});