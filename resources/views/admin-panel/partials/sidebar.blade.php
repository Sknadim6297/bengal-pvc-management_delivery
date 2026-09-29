<div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar">
	<div class="offcanvas-header border-bottom">
		<h5 class="fw-bold mb-0">Admin Panel</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0">
		<div class="sidebar-brand">
			<img src="{{ asset('assets/img/pvc_logo.png') }}" alt="India PVC logo">
		</div>
		<div class="sidebar-user">
			<small>ADMIN PANEL</small>
			<h5>{{ auth()->user()->name }}</h5>
		</div>
		<nav class="sidebar-menu">
			<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
			<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i><span>Users</span></a>
		</nav>
	</div>
</div>

<div class="col-lg-3 col-xl-3 desktop-sidebar">
	<div class="sidebar">
		<div class="sidebar-brand">
			<img src="{{ asset('assets/img/pvc_logo.png') }}" alt="India PVC logo">
		</div>
		<div class="sidebar-user">
			<small>ADMIN PANEL</small>
			<h5>{{ auth()->user()->name }}</h5>
		</div>
		<nav class="sidebar-menu">
			<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
			<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i><span>Users</span></a>
		</nav>
	</div>
</div>
