@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Account Registration</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active text-white">Create Account</li>
    </ol>
</div>

<div class="container-fluid  mt-5">
    <div class="container ">
        <div class="row g-4 mb-5">

            <div class="col-lg-8 col-xl-8">
                <div class="row g-4">
                    <div class="col-lg-12">

                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <div class="form-item w-100">
                                    <label class="form-label my-3" for="firstName">First Name</label>
                                    <input type="text" class="form-control" id="firstName">
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-6">
                                <div class="form-item w-100">
                                    <label class="form-label my-3" for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName">
                                </div>
                            </div>
                        </div>

                        <div class="form-item">
                            <label class="form-label my-3" for="email">Email Address</label>
                            <input type="email" class="form-control" id="email">
                        </div>

                        <div class="form-item">
                            <label class="form-label my-3" for="mobile">Mobile</label>
                            <input type="tel" class="form-control" id="mobile">
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <label class="form-label my-3" for="mobile">Password</label>
                                <div class="mb-3 position-relative">
                                    <input type="password" id="passwordInput" class="form-control">
                                    <i class="fa-solid fa-eye position-absolute" id="togglePassword"
                                        style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-6">
                                <label class="form-label my-3" for="mobile">Confirm Password</label>
                                <div class="mb-3 position-relative">
                                    <input type="password" id="confirmpasswordInput" class="form-control">
                                    <i class="fa-solid fa-eye position-absolute" id="togglePasswordConfirm"
                                        style="top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="my-3 d-flex gap-2">
                            <button type="submit" id="submitBtn" class="btn btn-primary text-white">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>

                            <a href="{{ url('user-login') }}" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Have an account? Login
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Featured Products Section -->
            <div class="col-lg-4 col-xl-4">
                <div class="row g-4 fruite">
                    <div class="col-lg-12">
                        <div class="position-relative">
                            <img src="assets/img/banner-fruits.jpg" class="img-fluid w-100 rounded" alt="">
                            <div class="position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%);">
                                <h3 class="text-white fw-bold">Shanana <br> Beauty <br> Products</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordInput = document.getElementById('passwordInput');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    const confirmpasswordInput = document.getElementById('confirmpasswordInput');
    togglePasswordConfirm.addEventListener('click', function () {
        const type = confirmpasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmpasswordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

<script>
    $(document).ready(function () {
        $('button[type="submit"]').on('click', function (e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true);
            submitBtn.html('Creating user account... <i class="fas fa-spinner fa-spin"></i>');

            let isValid = true;
            let missingFields = [];

            let requiredFields = [{
                id: 'firstName',
                name: 'First Name'
            },
            {
                id: 'lastName',
                name: 'Last Name'
            },
            {
                id: 'email',
                name: 'Email'
            },
            {
                id: 'mobile',
                name: 'Mobile'
            },
            {
                id: 'passwordInput',
                name: 'Password'
            },
            {
                id: 'confirmpasswordInput',
                name: 'Confirm Password'
            }
            ];

            $('.form-control').removeClass('is-invalid');

            requiredFields.forEach(field => {
                let value = $('#' + field.id).val().trim();
                if (value === '') {
                    $('#' + field.id).addClass('is-invalid');
                    isValid = false;
                    missingFields.push(field.name);
                }
            });

            const email = $('#email').val().trim();
            let emailError = '';

            if (email) {
                if (!email.includes('@')) {
                    emailError = 'Email must include "@" symbol';
                } else if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    emailError = 'Email must have a valid domain (e.g. example@domain.com)';
                }
            }

            if (emailError) {
                isValid = false;
                $('#email').addClass('is-invalid');
                missingFields.push(emailError);
            }

            const password = $('#passwordInput').val().trim();
            const confirmPassword = $('#confirmpasswordInput').val().trim();

            const passwordPattern = {
                length: /.{8,}/,
                lowercase: /[a-z]/,
                uppercase: /[A-Z]/,
                digit: /\d/,
                specialChar: /[\W_]/,
            };

            if (password && confirmPassword && password !== confirmPassword) {
                isValid = false;
                $('#passwordInput, #confirmpasswordInput').addClass('is-invalid');
                missingFields.push('Passwords do not match');
            } else if (password && confirmPassword) {
                let patternErrors = [];

                if (!passwordPattern.length.test(password)) patternErrors.push(
                    '• Minimum 8 characters');
                if (!passwordPattern.lowercase.test(password)) patternErrors.push(
                    '• At least one lowercase letter');
                if (!passwordPattern.uppercase.test(password)) patternErrors.push(
                    '• At least one uppercase letter');
                if (!passwordPattern.digit.test(password)) patternErrors.push('• At least one number');
                if (!passwordPattern.specialChar.test(password)) patternErrors.push(
                    '• At least one special character');

                if (patternErrors.length > 0) {
                    isValid = false;
                    $('#passwordInput').addClass('is-invalid');
                    missingFields.push('Password must include:<br>' + patternErrors.join('<br>'));
                }
            }

            if (!isValid) {
                submitBtn.prop('disabled', false);
                submitBtn.html('Create Account');

                let listItems = missingFields.map(name => `<li>${name}</li>`).join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    html: `
                        <p style="text-align: left;">Please fill in the following required fields:</p>
                        <ol style="text-align: left; padding-left: 20px;">${listItems}</ol>
                    `,
                });
                return;
            }

            let form_data = new FormData();
            requiredFields.forEach(field => {
                form_data.append(field.id, $('#' + field.id).val().trim());
            });

            $.ajax({
                type: "POST",
                url: "/store-user-information",
                data: form_data,
                processData: false,
                contentType: false,
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'User Account created successfully!',
                    }).then(() => {
                        sessionStorage.setItem('showWelcome', 'true');
                        if (data.redirect_url) {
                            window.location.href = data
                                .redirect_url;
                        } else {
                            location.reload();
                        }
                    });
                    submitBtn.prop('disabled', false);
                    submitBtn.html('Create Account');
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false);
                    submitBtn.html('Create Account');

                    let errorMessage = 'An error occurred. Please try again.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Could not create account',
                        text: errorMessage,
                    });
                    console.error(xhr);
                }
            });
        });
    });
</script>

@include('layouts.footer')