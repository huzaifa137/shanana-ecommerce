<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Shanana Beauty Products</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="/assets/lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="/assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50  d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->


    <!-- Navbar start -->
    <div class="container-fluid fixed-top">
        <div class="container topbar bg-primary d-none d-lg-block">
            <div class="d-flex justify-content-between">
                <div class="top-info ps-2">
                    <small class="me-3"><i class="fas fa-map-marker-alt me-2 text-white"></i> <a href="#"
                            class="text-white">Junction Mall, Namugongo Road, Kireka, Uganda</a></small>
                    <small class="me-3"><i class="fas fa-envelope me-2 text-white"></i><a href="#"
                            class="text-white">shananabeauty120@gmail.com</a></small>
                </div>
                <div class="top-link pe-2">
                    <a href="#" class="text-white"><small class="text-white mx-2">Shanana Beauty and Bedroom Products</small></a>
                </div>
            </div>
        </div>
        <div class="container px-0">
            <nav class="navbar navbar-light bg-white navbar-expand-xl">
                <a href="{{ url('/') }}" class="navbar-brand">
                    <h1 class="text-primary display-6">Shanana Products</h1>
                </a>
                <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>
                <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                    <div class="navbar-nav mx-auto">
                        <a href="{{ url('/') }}" class="nav-item nav-link">Home</a>
                        <a href="{{ url('item-shop') }}" class="nav-item nav-link">Shop</a>

                        @if (Session::has('LoggedCustomer'))
                            <?php
                            $addedProducts = Session::get('cart', []);
                            $cartCount = count($addedProducts);
                            ?>

                            @if ($cartCount > 0)
                                <a href="{{ url('item-cart') }}"
                                    class="nav-item nav-link position-relative d-inline-block">
                                    <span class="position-relative d-inline-flex align-items-center">
                                        Cart
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger animate__animated animate__bounce"
                                            style="width: 20px; height: 20px; font-size: 12px; right: -6px; display: flex; justify-content: center; align-items: center;">
                                            {{ $cartCount }}
                                            <span class="visually-hidden">items in cart</span>
                                        </span>
                                    </span>
                                </a>
                            @else
                                <a href="{{ url('item-cart') }}" class="nav-item nav-link">Cart</a>
                            @endif
                        @else
                            <a href="{{ url('item-cart') }}" class="nav-item nav-link">Cart</a>
                        @endif

                        <a href="{{ route('order.track') }}" class="nav-item nav-link">Track Order</a>
                        <a href="{{ url('contact-us') }}" class="nav-item nav-link">Contact Us</a>

                        @if (!Session::has('LoggedCustomer') && !Session::has('LoggedAdmin'))
                            <li class="nav-item active">
                                <a href="{{ url('/user-login') }}" class="nav-link">Login</a>
                            </li>
                            <li class="nav-item active">
                                <a href="{{ url('user-register') }}" class="nav-link">Create Account</a>
                            </li>
                        @else
                            <li class="nav-item active">
                                <a href="{{ url('/customer/dashboard') }}" class="nav-link">Dashboard</a>
                            </li>
                        @endif

                    </div>
                    <div class="d-flex m-3 me-0">

                        <button class="btn p-0 me-4 my-auto" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch">
                            <i class="bi bi-search fs-2"></i>
                        </button>


                        @if (Session::has('LoggedCustomer'))
                            <?php
                            $addedProducts = Session::get('cart', []);
                            $cartCount = count($addedProducts);
                            ?>

                            <a href="{{ url('item-cart') }}" class="position-relative me-4 my-auto">
                                <i class="fa fa-shopping-bag fa-2x"></i>
                                <span
                                    class="position-absolute bg-secondary rounded-circle d-flex align-items-center justify-content-center text-dark px-1"
                                    style="top: -5px; left: 15px; height: 20px; min-width: 20px;">{{ $cartCount }}</span>
                            </a>
                        @else
                            <a href="{{ url('item-cart') }}" class="position-relative me-4 my-auto">
                                <i class="fa fa-shopping-bag fa-2x"></i>
                            </a>
                        @endif


                        <link rel="stylesheet"
                            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

                        <style>
                            #offcanvasSearch {
                                background-color: #f8f9fa;
                                /* Light grey background for contrast */
                                color: #212529;
                                /* Dark text for readability */
                                border-right: 1px solid #dee2e6;
                            }

                            #productSearchInput {
                                border-radius: 0.375rem;
                                /* Rounded corners */
                                padding: 0.75rem;
                                font-size: 1rem;
                            }

                            #searchResults .list-group-item {
                                transition: background-color 0.2s;
                            }

                            #searchResults .list-group-item:hover {
                                background-color: #e9ecef;
                                cursor: pointer;
                            }

                            .offcanvas-header {
                                border-bottom: 1px solid #dee2e6;
                            }
                        </style>

                        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSearch">
                            <div class="offcanvas-header justify-content-between">
                                <h4 class="fw-bold text-uppercase fs-6" style="color: #ff85c1;">Search Products</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <input type="text" id="productSearchInput" class="form-control mb-3 shadow-sm"
                                    placeholder="Search products...">
                                <div id="searchResults" class="list-group">
                                    <!-- Matching products will be inserted here -->
                                </div>
                            </div>
                        </div>


                        <script>
                            document.getElementById('productSearchInput').addEventListener('input', function() {
                                let query = this.value;
                                if (query.length < 2) {
                                    document.getElementById('searchResults').innerHTML = '';
                                    return;
                                }

                                fetch(`/search-products?query=${encodeURIComponent(query)}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        let resultHTML = '';
                                        if (data.length > 0) {
                                            data.forEach(product => {
                                                resultHTML += `
                            <a href="/product-item/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <img src="/storage/${product.featured_image_1}" alt="${product.product_name}" width="50" height="50" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold">${product.product_name}</div>
                                    <div class="text-muted">Ugx${parseFloat(product.sale_price || product.price).toLocaleString()}</div>
                                </div>
                            </a>
                        `;
                                            });
                                        } else {
                                            resultHTML = `<div class="list-group-item text-muted">No matching products found</div>`;
                                        }

                                        document.getElementById('searchResults').innerHTML = resultHTML;
                                    });
                            });
                        </script>


                        @if (Session('LoggedCustomer'))
                            <div class="dropdown">
                                <button class="btn btn-primary text-white dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user fa-1x"></i>
                                </button>

                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('user.profile') }}">
                                            <i class="fas fa-user me-2"></i> Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('customer.logout') }}">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>

                            </div>
                        @else
                            <a href="{{ url('user-login') }}" class="my-auto">
                                <i class="fas fa-user fa-2x"></i>
                            </a>
                        @endif


                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->


    <!-- Modal Search Start -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Search by keyword</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex align-items-center">
                    <div class="input-group w-75 mx-auto d-flex">
                        <input type="search" class="form-control p-3" placeholder="keywords"
                            aria-describedby="search-icon-1">
                        <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Search End -->
