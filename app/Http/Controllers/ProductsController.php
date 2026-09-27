<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    public function productCategories()
    {
        $categories = Category::all();

        return view('Products.all-categories', compact(['categories']));
    }

    public function addCategory()
    {
        $categories = Category::all();

        return view('Products.create-category', compact(['categories']));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'parent'         => 'required',
            'status'         => 'required|in:10,0,1',
            'description'    => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $imagePath = null;

        if ($request->hasFile('featured_image')) {
            $image     = $request->file('featured_image');
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            // Store image in storage/app/public/featured_images and get relative path
            $imagePath = $image->storeAs('featured_images', $imageName, 'public');
        }

        $category = Category::create([
            'name'           => $validated['name'],
            'parent_id'      => $validated['parent'] ?: null,
            'status'         => $validated['status'],
            'description'    => $validated['description'],
            'featured_image' => $imagePath,
        ]);

        return response()->json([
            'message'  => 'Category created successfully!',
            'category' => $category,
        ]);
    }

    public function editCategory($id)
    {
        $category   = Category::findOrFail($id);
        $categories = Category::all();

        return view('Products.edit-category', compact('category', 'categories'));
    }

    public function updateCategory(Request $request, $id)
    {

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'parent_id'      => 'nullable|integer',
            'status'         => 'required|in:10,0,1',
            'description'    => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $category = Category::findOrFail($id);

        if ($request->hasFile('featured_image')) {
            $image                    = $request->file('featured_image');
            $imageName                = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $imagePath                = $image->storeAs('featured_images', $imageName, 'public');
            $category->featured_image = $imagePath;
        }

        $category->name        = $validated['name'];
        $category->parent_id   = $request->input('parent_id') ?: null;
        $category->status      = $validated['status'];
        $category->description = $validated['description'] ?? null;

        $category->save();

        return response()->json([
            'message'  => 'Category updated successfully!',
            'category' => $category,
        ]);
    }

    public function deleteCategory(Category $category)
    {
        try {

            if ($category->featured_image_1) {
                Storage::delete($category->featured_image_1);
            }

            $category->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Delete failed'], 500);
        }
    }

    public function allProducts()
    {
        $products = Product::latest()->get();
        return view('Products.all-products', compact('products'));
    }

    public function addProduct()
    {
        $categories = Category::all();

        return view('Products.add-product', compact(['categories']));
    }

    public function editProduct($id)
    {
        $product    = Product::findOrFail($id);
        $categories = Category::all();

        $labels = $product->labels ?? [];
        $taxes  = $product->taxes ?? [];

        $isCombo = $product->is_combo;

        $reviews = ProductReview::where('product_id', $product->id)->get();

        return view('Products.edit-product', compact('product', 'categories', 'labels', 'taxes', 'reviews', 'isCombo'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'product_name'     => 'required|string',
            'categories'       => 'nullable|string', // JSON string of category IDs
            'status'           => 'required|integer',
            'description'      => 'nullable|string',
            'price'            => 'required|string',
            'sale_price'       => 'required|string',
            'quantity'         => 'required|integer',
            'sku'              => 'nullable|string',
            'attributes'       => 'nullable|string',
            'labels'           => 'nullable|string',
            'taxes'            => 'nullable|string',
            'product_combo'    => 'required|string',
            'featured_image_1' => 'nullable|image',
            'featured_image_2' => 'nullable|image',
            'featured_image_3' => 'nullable|image',
            'reviews'          => 'nullable|string',
        ]);

        // Create product
        $product = new Product($validated);

        $product->attributes = json_decode($request->input('attributes'), true);
        $product->labels     = json_decode($request->input('labels'), true);
        $product->taxes      = json_decode($request->input('taxes'), true);

        // Handle combo field
        $productCombo      = json_decode($request->input('product_combo'), true);
        $product->is_combo = $productCombo['yesCombo'] ?? false;

        // Handle image uploads
        foreach ([1, 2, 3] as $index) {
            $key = "featured_image_{$index}";
            if ($request->hasFile($key)) {
                $path          = $request->file($key)->store("products", "public");
                $product->$key = $path;
            }
        }

        $product->save();

        // Save associated reviews
        $reviews = json_decode($request->input('reviews'), true);
        if (is_array($reviews)) {
            foreach ($reviews as $review) {
                ProductReview::create([
                    'product_id'     => $product->id,
                    'reviewer_name'  => $review['name'],
                    'reviewer_email' => $review['email'] ?? null,
                    'rating'         => $review['rating'] ?? null,
                    'review_message' => $review['message'],
                    'review_date'    => $review['date'] ?? null,
                ]);
            }
        }

        // ✅ Attach categories if any were selected
        $categoryIds = json_decode($request->input('categories'), true);
        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $product->categories()->sync($categoryIds);
        }

        return response()->json([
            'message'    => 'Product saved successfully.',
            'product_id' => $product->id,
        ]);
    }

    public function storeReview(Request $request)
    {
        $reviews = json_decode($request->input('reviews'), true);

        if (is_array($reviews)) {
            foreach ($reviews as $review) {
                ProductReview::create([
                    'product_id'     => $request->productId,
                    'reviewer_name'  => $review['name'],
                    'reviewer_email' => $review['email'] ?? null,
                    'rating'         => $review['rating'] ?? null,
                    'review_message' => $review['message'],
                    'review_date'    => $review['date'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product review saved successfully.',
        ]);
    }

    public function customerStoreReview(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'rating'     => 'required|integer|min:1|max:5',
            'message'    => 'required|string',
            'date'       => 'required|date',
        ]);

        $customer = User::where('id', Session('LoggedCustomer'))->first();

        // Check for existing review
        $existingReview = ProductReview::where('product_id', $request->product_id)
            ->where('reviewer_email', $customer->email)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already submitted a review for this product.',
            ], 409); // 409 = Conflict
        }

        ProductReview::create([
            'product_id'     => $request->product_id,
            'reviewer_name'  => $request->name,
            'reviewer_email' => $customer->email,
            'rating'         => $request->rating,
            'review_message' => $request->message,
            'review_date'    => $request->date,
        ]);

        return response()->json(['message' => 'Thank you! Your review has been posted.']);
    }

    public function deleteProduct(Product $product)
    {
        try {

            if ($product->featured_image_1) {
                Storage::delete($product->featured_image_1);
            }

            $product->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Delete failed'], 500);
        }
    }

    public function deleteReview($id)
    {

        $review = ProductReview::findOrFail($id);
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully.']);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name'     => 'required|string|max:255',
                                                                  // 'category'         => 'required|integer',
            'categories.*'     => 'integer|exists:categories,id', // validate each ID
            'status'           => 'required|integer',
            'description'      => 'required|string',
            'price'            => 'required|numeric',
            'sale_price'       => 'required|numeric',
            'quantity'         => 'required|integer',
            'sku'              => 'required|string|max:255',

            'attributes'       => 'nullable|array',
            'labels'           => 'nullable|string',
            'taxes'            => 'nullable|string',

            'product_combo'    => 'required|string',
            'featured_image_1' => 'nullable|image|max:2048',
            'featured_image_2' => 'nullable|image|max:2048',
            'featured_image_3' => 'nullable|image|max:2048',
            'reviews'          => 'nullable|string',
            'deleted_reviews'  => 'nullable|string',
        ]);

        $product->product_name = $request->product_name;
        // $product->category     = $request->category;
        $product->status      = $request->status;
        $product->description = $request->description;
        $product->price       = $request->price;
        $product->sale_price  = $request->sale_price;
        $product->quantity    = $request->quantity;
        $product->sku         = $request->sku;

        $product->attributes = $request->input('attributes', []);

        $product->labels = json_decode($request->input('labels', '[]'), true);
        $product->taxes  = json_decode($request->input('taxes', '[]'), true);

        $productComboData  = json_decode($request->input('product_combo'), true);
        $product->is_combo = $productComboData['yesCombo'] ?? false;

        for ($i = 1; $i <= 3; $i++) {
            $fileKey = "featured_image_{$i}";
            if ($request->hasFile($fileKey)) {
                if ($product->$fileKey && Storage::disk('public')->exists($product->$fileKey)) {
                    Storage::disk('public')->delete($product->$fileKey);
                }
                $path              = $request->file($fileKey)->store('products', 'public');
                $product->$fileKey = $path;
            }
        }

        $product->save();

        $deletedReviews = json_decode($request->input('deleted_reviews'), true);
        if (is_array($deletedReviews)) {
            ProductReview::whereIn('id', $deletedReviews)
                ->where('product_id', $product->id)
                ->delete();
        }

        $product->categories()->sync($request->categories);

        $reviews = json_decode($request->input('reviews'), true);
        if (is_array($reviews)) {
            foreach ($reviews as $review) {
                if (empty($review['name']) || empty($review['message'])) {
                    continue;
                }

                if (! empty($review['id'])) {
                    $existing = ProductReview::where('id', $review['id'])
                        ->where('product_id', $product->id)
                        ->first();
                    if ($existing) {
                        $existing->update([
                            'reviewer_name'  => $review['name'],
                            'reviewer_email' => $review['email'] ?? null,
                            'rating'         => $review['rating'] ?? null,
                            'review_message' => $review['message'],
                            'review_date'    => $review['date'] ?? null,
                        ]);
                    }
                } else {
                    ProductReview::create([
                        'product_id'     => $product->id,
                        'reviewer_name'  => $review['name'],
                        'reviewer_email' => $review['email'] ?? null,
                        'rating'         => $review['rating'] ?? null,
                        'review_message' => $review['message'],
                        'review_date'    => $review['date'] ?? null,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Product updated successfully']);
    }

    public function addToCart(Request $request, $id)
    {
        $product      = Product::findOrFail($id);
        $cart         = Session::get('cart', []);
        $cleanedPrice = (int) str_replace(',', '', $product->sale_price);

        $quantity = (int) $request->input('quantity', 1);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                "id"        => $product->id,
                "name"      => $product->product_name,
                "thumbnail" => $product->featured_image_1,
                "price"     => $cleanedPrice,
                "quantity"  => $quantity,
            ];
        }

        Session::put('cart', $cart);

        $cartCount = collect($cart)->sum('quantity');

        // The "Add to cart" buttons submit via AJAX so the button/page can
        // update in place instead of a full reload; keep the old redirect
        // as a fallback for a non-JS request.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Product added to cart.',
                'cart_count' => $cartCount,
                'product_id' => $product->id,
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            return redirect()->back()->with('success', 'Product removed from cart.');
        }

        return redirect()->back()->with('error', 'Product not found in cart.');
    }

    public function updateQuantity(Request $request)
    {
        $productId = $request->product_id;
        $quantity  = $request->quantity;

        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
        }

        session()->put('cart', $cart);

        $product   = Product::find($productId);
        $itemTotal = $product->sale_price * $quantity;

        $subtotal = 0;
        foreach ($cart as $id => $item) {
            $p = Product::find($id);
            $subtotal += $p->sale_price * $item['quantity'];
        }

        return response()->json([
            'success'    => true,
            'item_total' => $itemTotal,
            'subtotal'   => $subtotal,
        ]);
    }

    public function getExchangeRates()
    {
        $base    = 'UGX';
        $symbols = 'USD,EUR,GBP';

        $response = Http::get("https://api.exchangerate.host/latest", [
            'base'    => $base,
            'symbols' => $symbols,
        ]);

        return response()->json($response->json());
    }

}