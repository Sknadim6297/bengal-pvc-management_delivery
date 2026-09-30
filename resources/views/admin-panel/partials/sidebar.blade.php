<div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar">
	<div class="offcanvas-header border-bottom">
		<h5 class="fw-bold mb-0">Admin Panel</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0">
		<div class="sidebar-user">
			<small>ADMIN PANEL</small>
			<h5>{{ auth()->user()->name }}</h5>
		</div>
		<nav class="sidebar-menu">
			<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
			<a href="{{ route('admin.general-settings.edit') }}" class="{{ request()->routeIs('admin.general-settings.*') ? 'active' : '' }}"><i class="bi bi-gear-fill"></i><span>General Settings</span></a>
			<a href="{{ route('admin.pricing.edit') }}" class="{{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}"><i class="bi bi-tags-fill"></i><span>Pricing Settings</span></a>
			<a href="{{ route('admin.pvc-orders.index') }}" class="{{ request()->routeIs('admin.pvc-orders.*') ? 'active' : '' }}"><i class="bi bi-credit-card-2-front-fill"></i><span>PVC Orders</span></a>
			<a href="{{ route('admin.photo-orders.index') }}" class="{{ request()->routeIs('admin.photo-orders.*') ? 'active' : '' }}"><i class="bi bi-image-fill"></i><span>Photo Orders</span></a>
			<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i><span>All Users</span></a>
			<a href="{{ route('admin.security') }}" class="{{ request()->routeIs('admin.security') ? 'active' : '' }}"><i class="bi bi-shield-lock-fill"></i><span>Security Settings</span></a>
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button type="submit" class="admin-sidebar-logout"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
			</form>
		</nav>
	</div>
</div>

<div class="col-lg-3 col-xl-3 desktop-sidebar">
	<div class="sidebar">

		<div class="sidebar-user">
			<small>ADMIN PANEL</small>
			<h5>{{ auth()->user()->name }}</h5>
		</div>
		<nav class="sidebar-menu">
			<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
			<a href="{{ route('admin.general-settings.edit') }}" class="{{ request()->routeIs('admin.general-settings.*') ? 'active' : '' }}"><i class="bi bi-gear-fill"></i><span>General Settings</span></a>
			<a href="{{ route('admin.pricing.edit') }}" class="{{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}"><i class="bi bi-tags-fill"></i><span>Pricing Settings</span></a>
			<a href="{{ route('admin.pvc-orders.index') }}" class="{{ request()->routeIs('admin.pvc-orders.*') ? 'active' : '' }}"><i class="bi bi-credit-card-2-front-fill"></i><span>PVC Orders</span></a>
			<a href="{{ route('admin.photo-orders.index') }}" class="{{ request()->routeIs('admin.photo-orders.*') ? 'active' : '' }}"><i class="bi bi-image-fill"></i><span>Photo Orders</span></a>
			<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i><span>All Users</span></a>
			<a href="{{ route('admin.security') }}" class="{{ request()->routeIs('admin.security') ? 'active' : '' }}"><i class="bi bi-shield-lock-fill"></i><span>Security Settings</span></a>
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button type="submit" class="admin-sidebar-logout"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
			</form>
		</nav>
	</div>
</div>
