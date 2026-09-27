@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Login</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active text-white">Login</li>
    </ol>
</div>

<div class="container-fluid py-3 bg-light">
    <div class="container ">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-8">
                <h1 class="text-center">Welcome Back</h1>
                <p class="text-center">Please login to your account</p>
                <form id="loginForm" action="#" class="p-4 rounded shadow bg-white">
                    
                    @if (Session::get('success'))
                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                    @endif

                    @if (Session::get('fail'))
                        <div class="alert alert-danger">{{ Session::get('fail') }}</div>
                    @endif

                    <div class="mb-3">
                        <input type="tel" id="loginPhone" class="form-control" placeholder="Enter phone number (e.g. 0712345678)">
                    </div>
                    <div class="mb-3 position-relative">
                        <input type="password" id="loginPassword" class="form-control" placeholder="Enter password">
                        <i class="fa-solid fa-eye position-absolute" id="togglePassword"
                            style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                    <div class="mb-3 text-center">
                        <p class="fw-bold text-success">
                            New here?
                            <a href="{{ url('user-register') }}" class="text-primary text-decoration-underline">
                                Create your free account now →
                            </a>
                        </p>
                    </div>
                    <div class="d-grid">
                        <button type="submit" id="loginBtn" class="btn btn-primary text-white"><i
                                class="bi bi-check-circle"></i> Login</button>
                    </div>
                    <div class="col-12 mt-2">
                        <a href="{{ url('/user-forgot-password') }}">Forgot
                            password?</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<script type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<script>
    const togglePassword = document.getElementById('togglePassword');
    const loginPassword = document.getElementById('loginPassword');

    togglePassword.addEventListener('click', function() {
        const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        loginPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>


<script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            const loginBtn = $('#loginBtn');
            loginBtn.prop('disabled', true).html(
                'Logging in... <i class="fas fa-spinner fa-spin"></i>');

            const phone = $('#loginPhone').val().trim();
            const password = $('#loginPassword').val().trim();
            let errors = [];

            if (!phone) {
                $('#loginPhone').addClass('is-invalid');
                errors.push('Phone number is required.');
            } else if (!/^[0-9+\s\-()]{7,20}$/.test(phone)) {
                $('#loginPhone').addClass('is-invalid');
                errors.push('Enter a valid phone number.');
            } else {
                $('#loginPhone').removeClass('is-invalid');
            }

            if (!password) {
                $('#loginPassword').addClass('is-invalid');
                errors.push('Password is required.');
            } else {
                $('#loginPassword').removeClass('is-invalid');
            }

            if (errors.length > 0) {
                loginBtn.prop('disabled', false).html('Login');
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    html: errors.map(err => `<p>${err}</p>`).join('')
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '/user-login-credentials',
                data: {
                    phone: phone,
                    password: password,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'User Login successfully!',
                    }).then(() => {
                        sessionStorage.setItem('showWelcome', 'true');
                        if (data.redirect_url) {
                            window.location.href = data
                                .redirect_url;
                        }
                    });
                    loginBtn.prop('disabled', false);
                },
                error: function(xhr) {
                    loginBtn.prop('disabled', false).html('Login');

                    let message = 'Incorrect phone number or password.';

                    if (xhr.status === 401) {
                        message = 'Incorrect phone number or password.';
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Login Error',
                        text: message
                    });
                }
            });
        });
    });
</script>


@include('layouts.footer')
