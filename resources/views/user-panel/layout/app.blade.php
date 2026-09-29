<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'India PVC')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="{{ asset('assets/style.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    @include('user-panel.partials.header')

    <div class="dashboard-wrapper">
        <div class="container-fluid px-3 px-lg-5">
            <div class="row g-4">
                @include('user-panel.partials.siderbar')

                <div class="col-lg-9 col-xl-9">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @if (filled(config('services.whatsapp_contact_url')))
        <div class="whatsapp-floating">
            <a href="{{ config('services.whatsapp_contact_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Contact Bengal PVC on WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>