<div class="offcanvas offcanvas-start"
        tabindex="-1"
        id="mobileSidebar">

        <div class="offcanvas-header border-bottom">

            <h5 class="fw-bold mb-0">User Panel
            </h5>

            <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas">
            </button>

        </div>

        <div class="offcanvas-body p-0">
                        <div class="sidebar-user">
                <small>USER PANEL</small>
                <h5>{{ auth()->user()?->name ?? 'User' }}</h5>
            </div>

            <div class="sidebar-menu">

                <a href="{{ route('dashboard') }}" class="active">
                    <i class="bi bi-pie-chart-fill"></i>
                    Dashboard
                </a>

                <a href="{{ route('user.pvc-card-print') }}">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                    PVC Card Print
                </a>

                <a href="{{ route('user.photo-print') }}">
                    <i class="bi bi-image"></i>
                    Photo Print
                </a>

                <a href="{{ route('user.recover-failed-order') }}">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Recover Failed Order
                </a>

                <a href="{{ route('user.order-history') }}">
                    <i class="bi bi-clock-history"></i>
                    Order History
                </a>

                <a href="{{ route('user.track-help') }}">
                    <i class="bi bi-truck"></i>
                    Track & Help
                </a>

                <a href="{{ route('user.security') }}">
                    <i class="bi bi-shield-fill"></i>
                    Security Settings
                </a>

            </div>

        </div>
    </div>

<div class="col-lg-3 col-xl-3 desktop-sidebar">

                    <div class="sidebar">
                        <div class="sidebar-user">

                            <small>USER PANEL</small>

                            <h5>{{ auth()->user()?->name ?? 'User' }}</h5>

                        </div>

                        <div class="sidebar-menu">

                            <a href="{{ route('dashboard') }}" class="active">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard</span>
                            </a>

                            <a href="{{ route('user.pvc-card-print') }}">
                                <i class="bi bi-credit-card-2-front-fill"></i>
                                <span>PVC Card Print</span>
                            </a>

                            <a href="{{ route('user.photo-print') }}">
                                <i class="bi bi-image-fill"></i>
                                <span>Photo Print</span>
                            </a>

                            <a href="{{ route('user.recover-failed-order') }}">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>Recover Failed Order</span>
                            </a>

                            <a href="{{ route('user.order-history') }}">
                                <i class="bi bi-clock-history"></i>
                                <span>Order History</span>
                            </a>

                            <a href="{{ route('user.track-help') }}">
                                <i class="bi bi-truck"></i>
                                <span>Track &amp; Help</span>
                            </a>

                            <a href="{{ route('user.security') }}">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>Security Settings</span>
                            </a>

                        </div>

                    </div>

                </div>