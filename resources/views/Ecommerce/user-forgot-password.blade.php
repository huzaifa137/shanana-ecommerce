@include('layouts.header')

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Forgot Password</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active text-white">Forgot Password</li>
    </ol>
</div>

<div class="container-fluid py-3 bg-light">
    <div class="container ">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-8">
                <h1 class="text-center">Forgot Password</h1>
                <p class="text-center">Please enter your registered phone number. If your account has an email address, we'll send a reset link to it.</p>
                <form id="loginForm" action="{{ url('user-generate-forgot-password-link') }}"
                    class="p-4 rounded shadow bg-white" method="POST">
                    @csrf

                    @if (Session::get('success'))
                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                    @endif

                    @if (Session::get('fail'))
                        <div class="alert alert-danger">{{ Session::get('fail') }}</div>
                    @endif

                    <div class="mb-3">
                        <input type="tel" name="phone" id="loginPhone" class="form-control"
                            placeholder="Enter your phone number (e.g. 0712345678)">
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="loginBtn" class="btn btn-primary text-white">
                            <i class="bi bi-check-circle"></i> Send
                        </button>
                    </div>

                    <div class="col-12 mt-3">
                        <p class="fw-bold text-success">
                            <a href="{{ url('user-login') }}" class="text-primary text-decoration-none">Return to Login
                                →</a>
                        </p>
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
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            const phone = $('#loginPhone').val().trim();

            if (!phone) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Field',
                    text: 'Please enter your phone number.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (!/^[0-9+\s\-()]{7,20}$/.test(phone)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Phone Number',
                    text: 'Please enter a valid phone number.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to proceed with the submission?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $('#loginBtn');
                    btn.prop('disabled', true);
                    btn.html(
                        '<i class="fe fe-loader"></i> Sending...<i class="fas fa-spinner fa-spin"></i>'
                        );

                    $.ajax({
                        type: "POST",
                        url: $('#loginForm').attr('action'),
                        data: {
                            phone: phone,
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: response.status ? 'success' : 'error',
                                title: response.status ? 'Success!' :
                                    'Error',
                                text: response.message
                            }).then(() => {
                                if (response.status) {
                                    location.reload();
                                }
                            });

                            btn.prop('disabled', false);
                            btn.html('<i class="bi bi-check-circle"></i> Send');
                        },
                        error: function(xhr) {
                            let message = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });

                            btn.prop('disabled', false);
                            btn.html('<i class="bi bi-check-circle"></i> Send');
                        }
                    });

                }
            });
        });
    });

    document.addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            $('#loginBtn').trigger('click');
        }
    });
</script>

@include('layouts.footer')
