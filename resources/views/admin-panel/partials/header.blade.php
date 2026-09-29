<header class="top-header">
	<div class="container-fluid px-3 px-lg-5">
		<div class="d-flex align-items-center justify-content-between">
			<button class="mobile-menu-btn me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Open admin menu">
				<i class="bi bi-list"></i>
			</button>

			<a href="{{ route('home') }}" class="brand me-auto">
				<img src="{{ asset('assets/img/pvc_logo.png') }}" class="brand-logo" alt="India PVC logo">
				<div class="brand-name">India <span>PVC</span></div>
			</a>

			<div class="d-flex align-items-center gap-3">
				<div class="help-box"><i class="bi bi-headset"></i> Admin Panel</div>
				<div class="user-box"><i class="bi bi-person"></i> {{ auth()->user()->name }}</div>
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					<button type="submit" class="logout-btn" aria-label="Logout"><i class="bi bi-box-arrow-right"></i></button>
				</form>
			</div>
		</div>
	</div>
</header>
