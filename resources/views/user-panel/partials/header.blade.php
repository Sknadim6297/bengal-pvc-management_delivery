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

                    <img src="{{ $generalSettings->logoUrl() }}"
                        class="brand-logo"
                        alt="{{ $generalSettings->brand_name }}">

                    <div class="brand-name">
                        @hasSection('brand-label')
                            @yield('brand-label') <span>PVC</span>
                        @else
                            {{ $generalSettings->application_name }}
                        @endif
                    </div>

                </a>

                <div class="d-flex align-items-center gap-3">

                    <div class="help-box">
                        <i class="bi bi-headset"></i>
                        Helpline: {{ $generalSettings->helpline_number }}
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