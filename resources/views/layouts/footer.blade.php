<!-- Footer Start -->
<div class="container-fluid bg-dark text-white-50 footer pt-5 mt-5">
    <div class="container py-5">
        <div class="pb-4 mb-4" style="border-bottom: 1px solid rgba(226, 175, 24, 0.5) ;">
            <div class="row g-4">

                <div class="col-lg-9">
                    <a href="javascript:void(0);">
                        <h1 class="text-primary mb-0">Shanana Beauty Products</h1>
                        <p class="text-secondary mb-0">Get to Know Us</p>
                    </a>
                </div>

                <div class="col-lg-3">
                    <div class="d-flex justify-content-end pt-3">
                        <a class="btn  btn-outline-secondary me-2 btn-md-square rounded-circle" href=""><i
                                class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href=""><i
                                class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href=""><i
                                class="fab fa-youtube"></i></a>
                        <a class="btn btn-outline-secondary btn-md-square rounded-circle" href=""><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

            </div>
        </div>
        <div class="row g-5">
            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Why People Like Us!</h4>
                    <p class="mb-4">
                        We’re trusted for our high-quality, carefully curated beauty products, transparent ingredients,
                        and a commitment to empowering every customer’s unique beauty. Our seamless shopping experience
                        and exceptional customer care keep people coming back.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="d-flex flex-column text-start footer-item">
                    <h4 class="text-light mb-3">Shop Info</h4>
                    <a class="btn-link" href="{{ route('home') }}">Home</a>
                    <a class="btn-link" href="{{ route('item.shop') }}">Shop</a>
                    <a class="btn-link" href="{{ route('item.cart') }}">Cart</a>
                    <a class="btn-link" href="{{ route('user.login') }}">Login</a>
                    <a class="btn-link" href="{{ route('customer.dashboard') }}">Dashboard</a>
                    <a class="btn-link" href="{{ route('user.register') }}">Create Account</a>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="d-flex flex-column text-start footer-item">
                    <h4 class="text-light mb-3">Account</h4>
                    <a class="btn-link" href="{{ route('item.shop') }}">Shop All</a>
                    <a class="btn-link" href="{{ url('product-options/1') }}">Best Sellers</a>
                    <a class="btn-link" href="{{ url('product-options/2') }}">Featured Products</a>
                    <a class="btn-link" href="{{ url('product-options/4') }}">New Arrivals</a>
                    <a class="btn-link" href="{{ url('product-options/3') }}">Most Popular Products</a>
                    <a class="btn-link" href="{{ route('customer.dashboard') }}">Dashboard</a>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-light mb-3">Contact</h4>
                    <p>Address: Junction Mall, Namugongo Road, Kireka, Uganda</p>
                    <p>Email: shananabeauty120@gmail.com</p>
                    <p>Phone: +256 702 501 011</p>
                    <p>Payments Accepted</p>
                    <img src="/assets/img/payment.png" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="container-fluid copyright bg-dark py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <span class="text-light"><a href="#"><i class="fas fa-copyright text-light me-2"></i>Shanana
                        Beauty and Bedroom Products</a>, All right reserved.</span>
            </div>
            <div class="col-md-6 my-auto text-center text-md-end text-white">
                Designed & Developed by
                <a href="javascript:void(0);" style="text-decoration: none;">UgandanProgrammer</a>
            </div>
        </div>
    </div>
</div>
<!-- Copyright End -->



<!-- Back to Top -->
<a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i
        class="fa fa-arrow-up"></i></a>


<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/lib/easing/easing.min.js"></script>
<script src="/assets/lib/waypoints/waypoints.min.js"></script>
<script src="/assets/lib/lightbox/js/lightbox.min.js"></script>
<script src="/assets/lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="/assets/js/main.js"></script>

<!-- Toasts used for add-to-cart feedback, loaded globally so every page
     that renders an "Add to cart" button has it available. -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/cart-ajax.js"></script>
</body>

</html>
