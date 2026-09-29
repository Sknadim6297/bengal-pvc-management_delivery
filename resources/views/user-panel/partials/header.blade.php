<header class="top-header">

        <div class="container-fluid px-3 px-lg-5">

            <div class="d-flex align-items-center justify-content-between">

                <!-- Mobile Menu -->
                <button class="mobile-menu-btn me-2"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileSidebar">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="brand me-auto">

                    <!-- Replace with your logo -->
                    <img src="{{ asset('assets/img/pvc_logo.png') }}"
                        class="brand-logo"
                        alt="Bengal PVC">

                    <div class="brand-name">
                        @yield('brand-label', 'India') <span>PVC</span>
                    </div>

                </a>

                <div class="d-flex align-items-center gap-3">

                    <div class="help-box">
                        <i class="bi bi-headset"></i>
                        Helpline: +91 8900162634
                    </div>

                    <div class="user-box">
                        <i class="bi bi-person"></i>
                        {{ auth()->user()?->name ?? 'User' }}
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-btn" type="submit" aria-label="Logout" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </header>