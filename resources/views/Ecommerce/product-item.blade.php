@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Product Information</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active text-white">Product Information</li>
    </ol>
</div>
<!-- Single Page Header End -->

<?php
use App\Http\Controllers\Helper;
?>
<!-- Single Product Start -->
<div class="container-fluid py-5 mt-5">
    <div class="container py-5">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="row g-4 mb-5">
            <div class="col-lg-8 col-xl-9">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded">
                            <a href="#">
                                <img src="{{ asset('storage/' . $product->featured_image_2) }}"
                                    class="img-fluid rounded" alt="Image">
                            </a>
                        </div>
                    </div>

                    <style>
                        .btn-minus,
                        .btn-plus {
                            width: 30px;
                            height: 30px;
                            padding: 0;
                        }
                    </style>

                    <div class="col-lg-6">
                        <h4 class="fw-bold mb-3">{{ $product->product_name }}</h4>
                        <p class="mb-3">Category: {{ Helper::product_category_name($product->category) }}</p>
                        <h5 class="fw-bold mb-3">Ugx {{ $product->sale_price }}/=</h5>

                        <div class="d-flex mb-4">
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                        </div>
                        @php
                            use Illuminate\Support\Str;

                            $plainText = strip_tags($product->description);
                            $wordLimit = 40;

                            $shortDescription = Str::words($plainText, $wordLimit, '...');
                        @endphp

                        @php
                            $cart = session('cart', []);
                            $isInCart = array_key_exists($product->id, $cart);
                        @endphp

                        <p class="mb-4">{{ $shortDescription }}</p>

                        @if ($isInCart)
                            <div class="input-group quantity mb-4" style="width: 120px;">
                                <input type="text" class="form-control form-control-sm text-center border-0"
                                    value="{{ $cart[$product->id]['quantity'] ?? 1 }}" readonly />
                            </div>

                            <button type="button"
                                class="btn border border-success rounded-pill px-4 py-2 mb-4 text-success" disabled>
                                <i class="fa fa-check me-2 text-success"></i> In Cart
                            </button>
                        @else
                            <form method="POST" action="{{ route('shop.add.cart', $product->id) }}">
                                @csrf
                                <div class="input-group quantity mb-4" style="width: 120px;">
                                    <div class="input-group-btn">
                                        <button type="button"
                                            class="btn btn-sm btn-minus rounded-circle bg-light border change-qty"
                                            data-change="-1" data-product-id="{{ $product->id }}">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <input type="text" name="quantity"
                                        class="form-control form-control-sm text-center border-0 quantity-input"
                                        data-product-id="{{ $product->id }}" value="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                    <div class="input-group-btn">
                                        <button type="button"
                                            class="btn btn-sm btn-plus rounded-circle bg-light border change-qty"
                                            data-change="1" data-product-id="{{ $product->id }}">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary">
                                    <i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart
                                </button>
                            </form>
                        @endif

                    </div>

                    <div class="col-12">
                        <nav>
                            <div class="nav nav-tabs mb-3">
                                <button class="nav-link active border-white border-bottom-0" type="button"
                                    role="tab" id="nav-about-tab" data-bs-toggle="tab" data-bs-target="#nav-about"
                                    aria-controls="nav-about" aria-selected="true">Description</button>
                                <button class="nav-link border-white border-bottom-0" type="button" role="tab"
                                    id="nav-mission-tab" data-bs-toggle="tab" data-bs-target="#nav-mission"
                                    aria-controls="nav-mission" aria-selected="false">Reviews</button>
                            </div>
                        </nav>
                        <div class="tab-content mb-5">
                            <div class="tab-pane active" id="nav-about" role="tabpanel" aria-labelledby="nav-about-tab">
                                {!! $product->description !!}
                                <div class="px-2 mt-4">

                                    @php
                                        $attributes = is_string($product->attributes)
                                            ? json_decode($product->attributes, true)
                                            : $product->attributes;
                                        $labels = is_string($product->labels)
                                            ? json_decode($product->labels, true)
                                            : $product->labels;
                                        $taxes = is_string($product->taxes)
                                            ? json_decode($product->taxes, true)
                                            : $product->taxes;

                                        $labelDisplay =
                                            collect($labels)->filter(fn($v) => $v == '1')->keys()->implode(', ') ?:
                                            'None';
                                        $taxDisplay =
                                            collect($taxes)->filter(fn($v) => $v == '1')->keys()->implode(', ') ?:
                                            'None';
                                    @endphp


                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div
                                                class="row bg-light text-center align-items-center justify-content-center py-2">
                                                <div class="col-6">
                                                    <p class="mb-0">SKU</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-0">{{ $product->sku }}</p>
                                                </div>
                                            </div>

                                            <div class="row text-center align-items-center justify-content-center py-2">
                                                <div class="col-6">
                                                    <p class="mb-0">Available Stock</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-0">{{ $product->quantity }}</p>
                                                </div>
                                            </div>

                                            <div
                                                class="row bg-light text-center align-items-center justify-content-center py-2">
                                                <div class="col-6">
                                                    <p class="mb-0">Price</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-0">UGX {{ number_format($product->sale_price) }}
                                                    </p>
                                                </div>
                                            </div>

                                            @if (!empty($attributes))
                                                @foreach ($attributes as $attr)
                                                    <div
                                                        class="row text-center align-items-center justify-content-center py-2">
                                                        <div class="col-6">
                                                            <p class="mb-0">{{ ucfirst($attr['attribute']) }}</p>
                                                        </div>
                                                        <div class="col-6">
                                                            <p class="mb-0">{{ $attr['value'] }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                            <div
                                                class="row bg-light text-center align-items-center justify-content-center py-2">
                                                <div class="col-6">
                                                    <p class="mb-0">Labels</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-0">{{ ucfirst($labelDisplay) }}</p>
                                                </div>
                                            </div>

                                            <div
                                                class="row text-center align-items-center justify-content-center py-2">
                                                <div class="col-6">
                                                    <p class="mb-0">Tax</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-0">{{ ucfirst($taxDisplay) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                use Carbon\Carbon;
                            @endphp

                            <div class="tab-pane" id="nav-mission" role="tabpanel"
                                aria-labelledby="nav-mission-tab">
                                @foreach ($reviews as $review)
                                    <div class="d-flex">
                                        <img src="/assets/img/avatar.jpg" class="img-fluid rounded-circle p-3"
                                            style="width: 100px; height: 100px;" alt="">
                                        <div class="">

                                            <p class="mb-2" style="font-size: 14px;">
                                                {{ Carbon::parse($review->review_date)->format('d, M, Y') }}
                                            </p>

                                            <div class="d-flex justify-content-between">
                                                <h5>{{ $review->reviewer_name }}</h5>
                                                <div class="d-flex mb-3">
                                                    <i class="fa fa-star text-secondary"></i>
                                                    <i class="fa fa-star text-secondary"></i>
                                                    <i class="fa fa-star text-secondary"></i>
                                                    <i class="fa fa-star text-secondary"></i>
                                                    <i class="fa fa-star text-secondary"></i>
                                                </div>
                                            </div>
                                            <p>{{ $review->review_message }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tab-pane" id="nav-vision" role="tabpanel">
                                <p class="text-dark">Tempor erat elitr rebum at clita. Diam dolor diam ipsum et
                                    tempor sit. Aliqu diam
                                    amet diam et eos labore. 3</p>
                                <p class="mb-0">Diam dolor diam ipsum et tempor sit. Aliqu diam amet diam et eos
                                    labore.
                                    Clita erat ipsum et lorem et sit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <form id="reviewForm" action="#" method="POST">
                        @csrf
                        <h4 class="mb-5 fw-bold">Your experience matters — leave a review on this product</h4>
                        <div class="row g-4 border rounded bg-white p-4">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="col-lg-6">
                                <label class="form-label">Enter your fullname *</label>
                                <input type="text"
                                    class="form-control border-0 border-bottom border-primary bg-light px-2 py-2 shadow-sm"
                                    name="name" placeholder="John Doe" required>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">Enter your rating *</label>
                                <select
                                    class="form-control border-0 border-bottom border-primary bg-light px-2 py-2 shadow-sm"
                                    name="rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">Review Date *</label>
                                <input type="date"
                                    class="form-control border-0 border-bottom border-primary bg-light px-2 py-2 shadow-sm"
                                    name="date" min="{{ \Carbon\Carbon::now()->toDateString() }}" required>
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label mt-3">Your Review *</label>
                                <textarea name="message" class="form-control border-0 border-bottom border-primary bg-light px-2 py-2 shadow-sm"
                                    rows="5" placeholder="Write your review..." required></textarea>
                            </div>

                            <div class="col-lg-12 text-end mt-4">
                                <button type="submit"
                                    class="btn border border-secondary text-primary rounded-pill px-4 py-3 shadow-sm">
                                    📨 Post Comment
                                </button>
                            </div>
                        </div>
                    </form>
                    </div>

                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const reviewForm = document.getElementById('reviewForm');

                            reviewForm.addEventListener('submit', function(e) {
                                e.preventDefault();

                                const formData = new FormData(reviewForm);

                                const submitBtn = reviewForm.querySelector('button[type="submit"]');
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i> Submitting...`;

                                fetch("{{ route('customer.store.review') }}", {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: formData
                                    })
                                    .then(async response => {
                                        const contentType = response.headers.get("content-type");

                                        if (!response.ok) {
                                            if (contentType && contentType.includes("application/json")) {
                                                const errorJson = await response.json();
                                                throw new Error(errorJson.message || 'Something went wrong.');
                                            } else {
                                                const errorText = await response.text();
                                                throw new Error(errorText); // HTML fallback
                                            }
                                        }

                                        return response.json();
                                    })
                                    .then(data => {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: data.message || 'Review submitted successfully.',
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            location.reload(); // Reload the page to show the new review
                                        });
                                    })
                                    .catch(error => {
                                        const isHtml = error.message.trim().startsWith('<');

                                        if (isHtml) {
                                            // Replace body with raw HTML if Laravel returned a full HTML error page
                                            document.body.innerHTML = error.message;
                                        } else {
                                            Swal.fire('Error!', error.message || 'Something went wrong.', 'error');
                                        }

                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = '📨 Post Comment';
                                    });
                            });
                        });
                    </script>



                </div>
            </div>
            <div class="col-lg-4 col-xl-3">
                <div class="row g-4 fruite">
                    <div class="col-lg-12">
                        <div class="input-group w-100 mx-auto d-flex mb-4">
                            <input type="search" class="form-control p-3" placeholder="keywords"
                                aria-describedby="search-icon-1">
                            <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                        </div>
                        <div class="mb-4">
                            <h4>Categories</h4>
                            <ul class="list-unstyled fruite-categorie">
                                @foreach ($categories as $category)
                                    <?php
                                    $countCategory = DB::table('products')->where('category', $category->id)->count();
                                    ?>
                                    <li>
                                        <div class="d-flex justify-content-between fruite-name">
                                            <a href="{{ url('/item-categories/' . $category->id) }}"><i
                                                    class="fas fa-apple-alt me-2"></i>{{ $category->name }}</a>
                                            <span>({{ $countCategory }})</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <h4 class="mb-4">Featured Products</h4>

                        @foreach ($featuredProducts as $featuredProduct)
                            <div class="d-flex align-items-start mb-4">
                                <div class="me-3 flex-shrink-0">
                                    <img src="{{ asset('storage/' . $featuredProduct->featured_image_2) }}"
                                        class="img-fluid rounded border" alt="{{ $featuredProduct->product_name }}"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $featuredProduct->product_name }}
                                    </h6>

                                    <div class="d-flex mb-2">
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fa fa-star text-warning me-1" style="font-size: 14px;"></i>
                                        @endfor
                                    </div>

                                    <div class="d-flex flex-column">
                                        <span class="text-primary fw-semibold" style="font-size: 14px;">
                                            UGX {{ number_format($featuredProduct->sale_price) }}/=
                                        </span>
                                        <span class="text-decoration-line-through" style="color:#E30048;"
                                            style="font-size: 13px;">
                                            UGX {{ number_format($featuredProduct->price) }}/=
                                        </span>
                                    </div>

                                </div>
                            </div>
                        @endforeach

                    </div>

                </div>
            </div>
        </div>

        <h1 class="fw-bold mb-0">Related products</h1>

        <div class="vesitable">
            <div class="owl-carousel vegetable-carousel justify-content-center">
                @php $cart = session('cart', []); @endphp

                @foreach ($products as $product)
                    @php $isInCart = array_key_exists($product->id, $cart); @endphp

                    <div class="border border-primary rounded position-relative vesitable-item h-100">

                        <a href="{{ url('/product-item/' . $product->id) }}" class="stretched-link"></a>

                        <div class="vesitable-img">
                            <img src="{{ asset('storage/' . $product->featured_image_1) }}"
                                class="img-fluid w-100 rounded-top" alt="{{ $product->product_name }}">
                        </div>

                        <div class="text-white bg-primary px-3 py-1 rounded position-absolute"
                            style="top: 10px; right: 10px; z-index: 2;">
                            {{ Helper::product_category_name($product->category) }}
                        </div>

                        <div class="p-4 pb-0 rounded-bottom position-relative" style="z-index: 2;">
                            <h4 class="fw-bold">{{ $product->product_name }}</h4>

                            @php
                                $plainText = strip_tags($product->description);
                                $wordLimit = 15;
                                $shortDescription = Str::words($plainText, $wordLimit, '...');
                            @endphp

                            <p>{{ $shortDescription }}</p>

                            <div class="d-flex justify-content-between flex-lg-wrap">
                                <p class="text-dark fs-5 fw-bold">Ugx {{ $product->sale_price }}/=</p>

                                @if ($isInCart)
                                    <button type="button"
                                        class="btn border border-success rounded-pill px-3 py-1 mb-4 text-success"
                                        disabled style="z-index: 3; position: relative;">
                                        <i class="fa fa-check me-2 text-success"></i> In Cart
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('shop.add.cart', $product->id) }}"
                                        style="z-index: 3; position: relative;">
                                        @csrf
                                        <button type="submit"
                                            class="btn border border-primary rounded-pill px-3 py-1 mb-4 text-primary">
                                            <i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>



        <script>
            $(document).ready(function() {
                $('.change-qty').off('click').on('click', function() {
                    const productId = $(this).data('product-id');
                    const change = parseInt($(this).data('change'));
                    const input = $(`.quantity-input[data-product-id="${productId}"]`);
                    let quantity = parseInt(input.val()) || 1;

                    quantity += change;
                    if (quantity < 1) quantity = 1;

                    input.val(quantity);
                });
            });
        </script>

    </div>
</div>
<!-- Single Product End -->
@include('layouts.footer')
