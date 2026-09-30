@extends('admin-panel.layout.app')

@section('content')

    <div class="security-content text-center">

        <h2>Security Settings</h2>

        <form method="POST" action="{{ route('admin.security.password') }}" class="password-card mx-auto">
            @csrf

            <h4>Change Account Password</h4>

            <div class="mb-3">
                <input type="password"
                    name="current_password"
                    class="form-control"
                    placeholder="Current Password"
                    autocomplete="current-password">
            </div>

            <div class="mb-3">
                <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="New Password"
                    autocomplete="new-password">
            </div>

            <div class="mb-4">
                <input type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Confirm New Password"
                    autocomplete="new-password">
            </div>

            <button type="submit" class="update-btn">
                Update Password
            </button>
        </form>

    </div>

@endsection

@push('scripts')
    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        @if (session('toast_error'))
            <div class="toast align-items-center text-bg-danger border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('toast_error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('toast_success'))
            <div class="toast align-items-center text-bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">{{ session('toast_success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toast').forEach(function (toastElement) {
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            });
        });
    </script>
@endpush
