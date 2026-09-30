@extends('admin-panel.layout.app')

@section('title', 'General Settings | ' . $generalSettings->application_name)

@section('content')
    <div class="content-card">
        <div class="mb-4">
            <h1 class="welcome-title mb-1">General Settings</h1>
        </div>

        <form method="POST" action="{{ route('admin.general-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h2 class="h5 fw-bold mb-3">Application Branding</h2>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label for="application_name" class="form-label">Application Name</label>
                    <input id="application_name" name="application_name" class="form-control" value="{{ old('application_name', $settings->application_name) }}" maxlength="120" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="brand_name" class="form-label">Brand Name</label>
                    <input id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name', $settings->brand_name) }}" maxlength="120" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="logo" class="form-label">Application Logo</label>
                    <input id="logo" name="logo" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <img src="{{ $settings->logoUrl() }}" alt="Current application logo" class="mt-3" style="max-width: 220px; max-height: 100px; object-fit: contain">
                </div>
                <div class="col-12 col-md-6">
                    <label for="favicon" class="form-label">Favicon</label>
                    <input id="favicon" name="favicon" type="file" class="form-control" accept="image/png,image/webp">
                    <img src="{{ $settings->faviconUrl() }}" alt="Current favicon" class="mt-3" style="width: 48px; height: 48px; object-fit: contain">
                </div>
            </div>

            <h2 class="h5 fw-bold mb-3">Contact Information</h2>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label for="helpline_number" class="form-label">Helpline Number</label>
                    <input id="helpline_number" name="helpline_number" class="form-control" value="{{ old('helpline_number', $settings->helpline_number) }}" maxlength="32" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="whatsapp_contact_url" class="form-label">WhatsApp Contact URL</label>
                    <input id="whatsapp_contact_url" name="whatsapp_contact_url" type="url" class="form-control" value="{{ old('whatsapp_contact_url', $settings->whatsapp_contact_url ?? config('services.whatsapp_contact_url')) }}" maxlength="2048">
                </div>
            </div>

            <button type="submit" class="admin-search-button"><i class="bi bi-floppy" aria-hidden="true"></i> Save settings</button>
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
            <div class="toast align-items-center text-bg-success border-0 shadow" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
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
                new bootstrap.Toast(toastElement).show();
            });
        });
    </script>
@endpush