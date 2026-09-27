<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\password_reset_table;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Shippo;

class MasterController extends Controller
{
    public function home()
    {
        $products = Product::all();
        $categories = Category::all();

        $bestSellingProducts = Product::where('labels->bestSelling', true)->take(4)->get();
        $featuredProducts = Product::where('labels->featured', true)->take(4)->get();
        $popularProducts = Product::where('labels->popular', true)->take(4)->get();
        $newProducts = Product::where('labels->new', true)->take(4)->get();
        $combos = Product::where('is_combo', true)->take(3)->get();

        $popupProducts = $featuredProducts->take(15);

        return view('Ecommerce.home', compact(
            'products',
            'popupProducts',
            'bestSellingProducts',
            'featuredProducts',
            'popularProducts',
            'newProducts',
            'categories',
            'combos',
        ));
    }

    public function loadMoreProducts(Request $request, $type)
    {

        $offset = $request->input('offset', 0);
        $limit = 4;

        $query = Product::query();

        switch ($type) {
            case 'bestSelling':
                $query->where('labels->bestSelling', true);
                break;
            case 'featured':
                $query->where('labels->featured', true);
                break;
            case 'popular':
                $query->where('labels->popular', true);
                break;
            case 'new':
                $query->where('labels->new', true);
                break;
            case 'combos':
                $query->where('is_combo', true);
                break;
            default:
                return response('', 400);
        }

        $products = $query->skip($offset)->take($limit)->get();

        return view('Ecommerce.partials.product_cards', compact('products'))->render();
    }

    public function itemCategories($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $newCategoryProducts = $category->products()->get();

        $legacyProducts = Product::where('category', $categoryId)->get();

        $products = $newCategoryProducts->merge($legacyProducts)->unique('id');

        $categories = Category::all();
        $featuredProducts = Product::where('labels->featured', true)->paginate(5);
        $popupProducts = $featuredProducts->take(15);

        return view('Ecommerce.item-categories', compact(
            'products',
            'categories',
            'featuredProducts',
            'popupProducts',
            'category'
        ));
    }

    public function itemOptions($optionsId)
    {

        if ($optionsId == 1) {
            $products = Product::where('labels->bestSelling', true)->get();
        } elseif ($optionsId == 2) {
            $products = Product::where('labels->featured', true)->get();
        } elseif ($optionsId == 3) {
            $products = Product::where('labels->popular', true)->get();
        } elseif ($optionsId == 4) {
            $products = Product::where('labels->new', true)->get();
        }

        $categories = Category::all();
        $featuredProducts = Product::where('labels->featured', true)->paginate(5);
        $popupProducts = $featuredProducts->take(15);

        return view('Ecommerce.item-options', compact('products', 'categories', 'featuredProducts', 'popupProducts', 'optionsId'));
    }

    public function itemShop()
    {
        $products = Product::all();
        $categories = Category::all();
        $featuredProducts = Product::where('labels->featured', true)->paginate(5);
        $popupProducts = $featuredProducts->take(15);

        return view('Ecommerce.item-shop', compact('products', 'categories', 'featuredProducts', 'popupProducts'));
    }

    // public function itemCart()
    // {
    //     $addedProducts = Session::get('cart', []);

    //     return view('Ecommerce.item-cart', compact('addedProducts'));
    // }

    public function itemCart()
    {
        $addedProducts = Session::get('cart', []);

        $subtotal = 0;
        if (count($addedProducts) > 0) {
            foreach ($addedProducts as $products) {
                $product = DB::table('products')->where('id', $products['id'])->first();
                $price = (int) str_replace(',', '', $product->sale_price);
                $quantity = $products['quantity'] ?? 1;
                $subtotal += $price * $quantity;
            }
        }

        return view('Ecommerce.item-cart', compact('addedProducts', 'subtotal'));
    }

    public function itemCheckout()
    {
        $cart = session()->get('cart', []);
        $user = User::find(Session('LoggedCustomer'));

        return view('Ecommerce.item-checkout', compact('cart', 'user'));
    }

    public function itemDetails()
    {

        return view('Ecommerce.item-detail');
    }

    public function contactUs()
    {
        return view('Ecommerce.contact');
    }

    public function userLogin()
    {
        return view('Ecommerce.user-login');
    }

    public function userRegister()
    {
        return view('Ecommerce.user-register');
    }

    public function productItem($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $reviews = ProductReview::where('product_id', $product->id)->get();
        $featuredProducts = Product::where('labels->featured', true)->paginate(5);
        $products = Product::all();
        $product_id = $id;

        return view('Ecommerce.product-item', compact('product', 'categories', 'reviews', 'featuredProducts', 'products', 'product_id'));
    }

    public function userProfile()
    {
        $customer = User::where('id', Session('LoggedCustomer'))->first();

        return view('Ecommerce.user-profile', compact(['customer']));
    }

    public function userForgotPassword()
    {
        return view('Ecommerce.user-forgot-password');
    }

    public function storeUserInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postcode' => 'required|string|max:20',
            'mobile' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'passwordInput' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/\d/',
                'regex:/[\W_]/',
            ],
            'confirmpasswordInput' => 'required|same:passwordInput',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $password = trim($request->passwordInput);

        $user = User::create([
            'first_name' => $request->input('firstName'),
            'last_name' => $request->input('lastName'),
            'email' => $request->input('email'),
            'company_name' => $request->input('companyName'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'country' => $request->input('country'),
            'postcode' => $request->input('postcode'),
            'mobile' => $request->input('mobile'),
            'password' => Hash::make($password),
            'default_shipping_address' => $request->isDefaultAddress,
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();
        $userRole = $user->user_role;
        $userId = $user->id;

        $data = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'company' => $user->company_name,
            'address' => $user->address,
            'city' => $user->city,
            'country' => $user->country,
            'postcode' => $user->postcode,
            'mobile' => $user->mobile,
            'password' => trim($request->passwordInput),
            'title' => 'Shanana Beauty Products - User Account has been created successfully.',
        ];

        try {
            Mail::send('emails.user-account-created', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });
        } catch (Exception $e) {
            DB::table('users')->where('email', $user->email)->delete();
            return back()->with('error', 'Email Not, Check Internet or re-register');
        }

        if ($userRole != 1) {
            $request->session()->put('LoggedAdmin', $userId);
        } else {
            $request->session()->put('LoggedCustomer', $userId);
        }

        $url = '/shanana/dashboard';
        $url2 = session()->get('url.intended');
        $url3 = '/customer/dashboard';

        if ($userRole != 1) {

            if ($url2 != null) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Account created successfully,proceeding to dashboard',
                    'redirect_url' => $url2,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'User Account created successfully,proceeding to dashboard',
                'redirect_url' => $url,
            ]);

        } else {

            return response()->json([
                'status' => true,
                'message' => 'User Account created successfully,proceeding to dashboard',
                'redirect_url' => $url3,
            ]);
        }
    }

    public function userloginCredentials(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userInfo = User::where('email', '=', $request->email)->first();

        if (!$userInfo) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect password or Email being entered',
            ], 401);

        } else {
            if (Hash::check($request->password, $userInfo->password)) {

                $user = DB::table('users')->where('email', $request->email)->first();
                $userRole = $user->user_role;
                $userId = $user->id;

                if ($userRole != 1) {
                    $request->session()->put('LoggedAdmin', $userId);
                } else {
                    $request->session()->put('LoggedCustomer', $userId);
                }

                $url = '/';
                $url2 = session()->get('url.intended');
                $url3 = '/customer/dashboard';

                if ($userRole != 1) {

                    if ($url2 != null) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Login successfully',
                            'redirect_url' => $url2,
                        ]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Login successfully',
                        'redirect_url' => $url,
                    ]);

                } else {

                    return response()->json([
                        'status' => true,
                        'message' => 'Login successfully',
                        'redirect_url' => $url3,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect password or Email being entered',
                ], 401);
            }
        }
    }

    public function updateProfile(Request $request)
    {

        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email',
            'companyName' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'postcode' => 'required|string',
            'mobile' => 'required|string',
            'password' => 'nullable|confirmed|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[\W_]/',
        ]);

        $user = User::find(Session('LoggedCustomer'));

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->company_name = $request->companyName;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->country = $request->country;
        $user->postcode = $request->postcode;
        $user->mobile = $request->mobile;
        $user->default_shipping_address = $request->default_shipping_address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully!']);
    }

    public function createNewPassword($id)
    {
        $generated_id = url('password/reset/' . $id);
        $resetEntry = DB::table('password_reset_tables')->where('token', $generated_id)->first();

        if ($resetEntry) {
            if ($resetEntry->link_status == 0) {
                if (now()->diffInMinutes($resetEntry->created_at) <= 30) {
                    return view('Ecommerce.reset-password-2', compact(['generated_id']));
                } else {
                    return ('Ecommerce.user-forgot-password')->with('fail', 'This reset password link has expired');
                }
            } else {
                return redirect()->route('Ecommerce.user-forgot-password')->with('fail', 'This link has already been used, request for a new link');
            }
        } else {
            return redirect()->route('Ecommerce.user-forgot-password')->with('fail', 'Invalid Link');
        }
    }

    public function generateForgotPasswordLink(Request $request)
    {
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The email provided is not registered in the system.',
                ], 401);
            }

            return back()->withInput()->with('fail', 'The email provided is not registered in the system.');
        }

        $username = DB::table('users')
            ->where('email', $email)
            ->value(DB::raw("CONCAT(first_name, ' ', last_name) AS fullname"));

        $token = Str::random(60);
        $resetUrl = url('password/reset', $token);

        $post = new password_reset_table();

        $post->email = $email;
        $post->token = $resetUrl;
        $post->created_at = now();
        $post->save();

        $data = [
            'email' => $email,
            'username' => $username,
            'resetUrl' => $resetUrl,
            'title' => 'SHANANA BEAUTY AND BED PRODUCSTS : Reset Password Link',
        ];

        Mail::send('emails.reset_email', $data, function ($message) use ($data) {
            $message->to($data['email'], $data['email'])->subject($data['title']);
        });

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Reset link sent successfully to: ' . $email,
            ]);
        }

        return back()->with('success', 'Link has been sent to your email: ' . $email);
    }

    public function store_new_password(Request $request)
    {
        $request->validate(
            [
                'password' => ['required', 'string', 'min:6', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*?&#]/'],
            ],
            [
                'password.required' => 'The password field is required.',
                'password.string' => 'The password must be a string.',
                'password.min' => 'The password must be at least 6 characters.',
                'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one digit, and one special character.',
            ],
        );

        $password = $request->password;
        $confirm = $request->confirmPassword;
        $generated_id = $request->generated_id;

        if ($password !== $confirm) {
            return response()->json(['message' => 'Passwords do not match.'], 422);
        }

        $record = DB::table('password_reset_tables')->where('token', $generated_id)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        $user_email = $record->email;
        $new_password = Hash::make($password);

        DB::table('users')->where('email', $user_email)->update(['password' => $new_password]);

        DB::table('password_reset_tables')
            ->where('id', $record->id)
            ->update(['link_status' => 1]);

        return response()->json(['message' => 'Password has been updated successfully.']);
    }

    public function calculateShippingRate(Request $request)
    {
        Shippo::setApiKey(config('services.shippo.key'));

        try {
            $fromAddress = [
                "name" => "Shanana Beauty",
                "street1" => "Kampala Road",
                "city" => "Kampala",
                "zip" => "256",
                "country" => "UG",
                "phone" => "+256000000000",
                "email" => "support@shanana.com",
            ];

            $toAddress = [
                "name" => $request->firstName . ' ' . $request->lastName,
                "street1" => $request->address,
                "city" => $request->city,
                "zip" => $request->postcode,
                "country" => $request->country,
                "phone" => $request->mobile,
                "email" => $request->email,
            ];

            $parcel = [
                "length" => "10",
                "width" => "5",
                "height" => "8",
                "distance_unit" => "cm",
                "weight" => "1",
                "mass_unit" => "kg",
            ];

            $shipment = \Shippo_Shipment::create([
                "address_from" => $fromAddress,
                "address_to" => $toAddress,
                "parcels" => [$parcel],
                "async" => false,
                "carrier_accounts" => ["d3d008a7fb6e4354a736e836ee5da0a2"], // ✅ Inserted working carrier
            ]);

            if (!empty($shipment["rates"])) {
                $lowestRate = $shipment["rates"][0];

                return response()->json([
                    'success' => true,
                    'rate' => $lowestRate['amount'],
                    'currency' => $lowestRate['currency'],
                    'provider' => $lowestRate['provider'],
                    'service_level' => $lowestRate['servicelevel']['name'],
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No rates found.']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
