<!--app header-->
<div class="app-header header top-header">
    <div class="container">
        <div class="d-flex">
            <a class="header-brand text-left" href="{{ url('/') }}">
                <img src="{{ URL::asset('assets2/images/brand/logo.png') }}" class="header-brand-img desktop-lgo"
                    alt="Dashtic logo">
                <img src="{{ URL::asset('assets2/images/brand/logo1.png') }}" class="header-brand-img dark-logo"
                    alt="Dashtic logo">
                <img src="{{ URL::asset('assets2/images/brand/favicon.png') }}" class="header-brand-img mobile-logo"
                    alt="Dashtic logo">
                <img src="{{ URL::asset('assets2/images/brand/favicon1.png') }}"
                    class="header-brand-img darkmobile-logo" alt="Dashtic logo">
            </a>
            <a id="horizontal-navtoggle" class="animated-arrow hor-toggle"><span></span></a>
            <style>
              
                .animated-badge {
                    animation: pulse-animation 1.5s infinite;
                }

                @keyframes pulse-animation {
                    0% {
                        transform: scale(1);
                    }

                    50% {
                        transform: scale(1.1);
                    }

                    100% {
                        transform: scale(1);
                    }
                }

                .animated-dropdown {
                    animation: fadeInDown 0.3s ease-out;
                }

                @keyframes fadeInDown {
                    from {
                        opacity: 0;
                        transform: translate3d(0, -10px, 0);
                    }

                    to {
                        opacity: 1;
                        transform: translate3d(0, 0, 0);
                    }
                }

              .dropdown-item {
                transition: background-color 0.2s ease, color 0.2s ease;
                padding: 0.75rem 1rem;
            }

            .dropdown-item:hover {
                background-color: var(--bs-primary);
                color: white !important;
            }

            .dropdown-item svg {
                transition: fill 0.2s ease;
            }

            .dropdown-item:hover svg {
                fill: white !important;
            }

            .dropdown-header {
                border-bottom: 1px solid var(--bs-border-color);
                padding: 0.75rem 1rem 0.5rem;
                margin-bottom: 0.5rem;
                font-size: 0.85em;
                color: var(--bs-secondary) !important;
            }

            .header-icon {
                vertical-align: middle;
            }

                .animated-badge {
                    width: 24px;
                    height: 24px;
                    padding: 0;
                    display: inline-flex;
                    justify-content: center;
                    align-items: center;
                    border-radius: 50% !important;
                    font-size: 0.75rem;
                }

                .animated-badge {
                    line-height: 1;
                    vertical-align: middle;
                }
            </style>

            <?php
            use App\Http\Controllers\Helper;
            ?>

            <div class="d-flex order-lg-2 ml-auto">
                <div class="dropdown profile-dropdown">
                    <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                        <span>
                            <img src="{{ URL::asset('assets2/images/users/16.jpg') }}" alt="img"
                                class="avatar avatar-md brround">
                        </span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="text-center">
                            <a href="javascript:void(0);" class="dropdown-item text-center user pb-0 font-weight-bold">{{{ Helper::fullname(Session('LoggedAdmin')) }}}</a>
                                
                            <span class="text-center user-semi-title">Administrator</span>
                            <div class="dropdown-divider"></div>
                        </div>

                        <a class="dropdown-item d-flex" href="{{ route('admin.customers.show', session('LoggedAdmin')) }}">
                            <svg class="header-icon mr-3" x="1008" y="1248" viewBox="0 0 24 24" height="100%"
                                width="100%" preserveAspectRatio="xMidYMid meet" focusable="false">
                                <path d="M0 0h24v24H0V0z" fill="none" />
                                <path d="M12 16c-2.69 0-5.77 1.28-6 2h12c-.2-.71-3.3-2-6-2z" opacity=".3" />
                                <circle cx="12" cy="8" opacity=".3" r="2" />
                                <path
                                    d="M12 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm-6 4c.22-.72 3.31-2 6-2 2.7 0 5.8 1.29 6 2H6zm6-6c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2z" />
                            </svg>
                            <div class="mt-1">Profile</div>
                        </a>

                        <a class="dropdown-item d-flex" href="{{ route('admin.logout') }}"
                            onclick="return confirm('Are you sure you want to log out?');">
                            <svg class="header-icon mr-3" x="1008" y="1248" viewBox="0 0 24 24" height="100%"
                                width="100%" preserveAspectRatio="xMidYMid meet" focusable="false">
                                <path d="M0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
                                <path d="M6 20h12V10H6v10zm2-6h3v-3h2v3h3v2h-3v3h-2v-3H8v-2z" opacity=".3" />
                                <path
                                    d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2H8.9V6zM18 20H6V10h12v10zm-7-1h2v-3h3v-2h-3v-3h-2v3H8v2h3z" />
                            </svg>
                            <div class="mt-1">Sign Out</div>
                        </a>

                        <form id="logout-form" action="#" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<!--/app header-->