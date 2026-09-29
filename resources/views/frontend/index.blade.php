<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>India PVC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/style.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
   
</head>
<body>
    <!-- ================= HEADER ================= -->

    <header class="main-header">

        <div class="container">

            <div class="d-flex align-items-center justify-content-between">

                <!-- LOGO -->
                <a href="{{ route('home') }}" class="brand-area">

                    <img src="{{ asset('assets/img/pvc_logo.png') }}"
                        class="brand-logo"
                        alt="India PVC">

                    <div class="brand-name">
                        India <span>PVC</span>
                    </div>

                </a>


                <!-- RIGHT -->
                <div class="d-flex align-items-center">

                    <a href="{{ route('login') }}" class="login-btn">Login
                </a>

                    <a href="{{ route('register') }}" class="register-btn">Register
                </a>

                </div>

            </div>

        </div>

    </header>



    <!-- ================= HERO ================= -->

    <section class="hero-section">

        <div class="container">

            <div class="hero-box">

                <div class="row align-items-center">


                    <!-- LEFT -->

                    <div class="col-lg-7">

                        <div class="trust-badge">

                            <span class="trust-dot"></span>

                            TRUSTED BY 1000+ CUSTOMERS

                   
                        </div>


                        <h1 class="hero-title">Professional Printing

                       

                            <span class="blue">PVC Cards • Photos
                        </span>

                            <span class="small-title">Across Bengal
                        </span>

                        </h1>


                        <p class="hero-description">
                            Normal & Premium PVC (800 micron), Custom Card Covers,
                        4x6 & A4 Photo Prints. Upload PDF or images, we print
                        premium quality and deliver in 3-7 days.

                   
                        </p>


                        <!-- BUTTONS -->

                        <div class="hero-buttons">

                            <a href="{{ route('login') }}" class="hero-btn order-btn">Start Order Now

                           

                                <i class="bi bi-arrow-right"></i>

                            </a>


                            <a href="#" class="hero-btn whatsapp-btn">

                                <i class="bi bi-whatsapp"></i>

                                WhatsApp Us

                        </a>


                            <a href="#" class="hero-btn channel-btn">

                                <i class="bi bi-megaphone"></i>

                                Join Channel

                        </a>

                        </div>

                    </div>



                    <!-- RIGHT -->

                    <div class="col-lg-5">

                        <div class="service-card">

                            <div class="service-grid">


                                <!-- AADHAAR -->

                                <div class="service-item">

                                    <i class="bi bi-person-vcard-fill"></i>

                                    <div class="service-name">
                                        AADHAAR
                               
                                    </div>

                                </div>


                                <!-- PAN -->

                                <div class="service-item">

                                    <i class="bi bi-credit-card-2-front-fill"></i>

                                    <div class="service-name">
                                        PAN
                               
                                    </div>

                                </div>


                                <!-- RATION -->

                                <div class="service-item">

                                    <i class="bi bi-basket-fill"></i>

                                    <div class="service-name">
                                        RATION
                               
                                    </div>

                                </div>


                                <!-- VOTER -->

                                <div class="service-item">

                                    <i class="bi bi-person-raised-hand"></i>

                                    <div class="service-name">
                                        VOTER
                               
                                    </div>

                                </div>

                            </div>


                            <div class="starting-price">
                                Starting at

                           

                                <strong>35</strong>

                                / card for Bulk order

                       
                            </div>

                        </div>

                    </div>

                </div>


                <!-- FLOATING WHATSAPP -->



            </div>

        </div>

    </section>

    <div class="container">
        <div class="row row-cols-2 row-cols-md-4 g-4 mb-5">

            <!-- Happy Customers -->
            <div class="col">
                <div class="stats-card bg-white rounded-4 p-4 text-center shadow-sm border h-100">
                    <div class="icon-box icon-indigo mx-auto mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">500+</h3>
                    <p class="small fw-semibold text-secondary mb-0">
                        Happy Customers
                    </p>
                </div>
            </div>

            <!-- Cards Printed -->
            <div class="col">
                <div class="stats-card bg-white rounded-4 p-4 text-center shadow-sm border h-100">
                    <div class="icon-box icon-green mx-auto mb-3">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">5000+</h3>
                    <p class="small fw-semibold text-secondary mb-0">
                        Cards Printed
                    </p>
                </div>
            </div>

            <!-- Districts Covered -->
            <div class="col">
                <div class="stats-card bg-white rounded-4 p-4 text-center shadow-sm border h-100">
                    <div class="icon-box icon-blue mx-auto mb-3">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">23</h3>
                    <p class="small fw-semibold text-secondary mb-0">
                        Districts Covered
                    </p>
                </div>
            </div>

            <!-- Customer Rating -->
            <div class="col">
                <div class="stats-card bg-white rounded-4 p-4 text-center shadow-sm border h-100">
                    <div class="icon-box icon-amber mx-auto mb-3">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">4.9/5</h3>
                    <p class="small fw-semibold text-secondary mb-0">
                        Customer Rating
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div class="mb-5 pb-4 container">

        <h2 class="display-6 fw-bold text-center text-dark mb-4">Our Professional Services
        </h2>

        <div class="row g-4">

            <!-- PVC Card Printing -->
            <div class="col-12 col-md-4">
                <div class="service-card bg-white rounded-4 p-4 shadow-sm border h-100">

                    <div class="service-icon icon-indigo mb-4">
                        <i class="bi bi-person-vcard-fill"></i>
                    </div>

                    <h3 class="fw-bold fs-5 mb-2">PVC Card Printing
                    </h3>

                    <p class="small text-secondary mb-3">
                        Normal &amp; Premium quality, 760-800 micron,
                    thermal lamination. Bulk discounts, free shipping 10+.
                    </p>

                    <span class="badge rounded-pill service-badge badge-indigo">Normal + Premium
                    </span>

                </div>
            </div>


            <!-- Card Covers -->
            <div class="col-12 col-md-4">
                <div class="service-card bg-white rounded-4 p-4 shadow-sm border h-100">

                    <div class="service-icon icon-amber mb-4">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>

                    <h3 class="fw-bold fs-5 mb-2">Card Covers &amp; Addons
                    </h3>

                    <p class="small text-secondary mb-3">
                        Custom printed transparent covers to protect your
                    premium cards. Rate configurable from admin.
                    </p>

                    <span class="badge rounded-pill service-badge badge-amber">Addon
                    </span>

                </div>
            </div>


            <!-- Custom Photo Prints -->
            <div class="col-12 col-md-4">
                <div class="service-card bg-white rounded-4 p-4 shadow-sm border h-100">

                    <div class="service-icon icon-pink mb-4">
                        <i class="bi bi-images"></i>
                    </div>

                    <h3 class="fw-bold fs-5 mb-2">Custom Photo Prints
                    </h3>

                    <p class="small text-secondary mb-3">
                        4x6 inch &amp; A4 size lab-quality glossy prints.
                    Upload photos, select size, doorstep delivery.
                    </p>

                    <span class="badge rounded-pill service-badge badge-pink">New
                    </span>

                </div>
            </div>

        </div>
    </div>

    <div class="mb-5 container">

        <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border">

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">

                <h3 class="h4 h-md-3 fw-bold text-dark d-flex align-items-center gap-2 mb-0">

                    <span class="live-dot">
                        <span class="live-dot-ping"></span>
                        <span class="live-dot-inner"></span>
                    </span>

                    Live Order Activity

                </h3>

                <span class="badge rounded-pill bg-light text-secondary px-3 py-2">Last 10 Orders
                </span>

            </div>


            <!-- Orders -->
            <div id="home-live-orders"
                class="row g-3 live-orders-container">
                <div class="col-12">
                    <div class="order-card justify-content-center text-secondary" role="status">
                        No live orders yet
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mb-5 container">

        <!-- Section Heading -->
        <div class="text-center mb-5">

            <h2 class="display-6 fw-bold text-dark mb-3">Why  PVC is
            <span class="text-primary">Trusted</span>
            </h2>

            <p class="text-secondary mx-auto trusted-subtitle">
                Premium materials, secure process, and guaranteed delivery
            across West Bengal
            </p>

        </div>


        <!-- Trust Cards -->
        <div class="row g-4">

            <!-- Premium -->
            <div class="col-12 col-md-4">

                <div class="trust-card trust-indigo h-100">

                    <div class="trust-icon icon-indigo mb-4">
                        <i class="bi bi-gem"></i>
                    </div>

                    <h3 class="h5 fw-bold text-dark mb-2">Premium 760 Micron PVC
                    </h3>

                    <p class="text-secondary lh-lg mb-0">
                        Not cheap plastic. Industrial-grade cards with
                    thermal lamination, waterproof and tear-proof.
                    </p>

                </div>

            </div>


            <!-- Secure -->
            <div class="col-12 col-md-4">

                <div class="trust-card trust-green h-100">

                    <div class="trust-icon icon-green mb-4">
                        <i class="bi bi-lock-fill"></i>
                    </div>

                    <h3 class="h5 fw-bold text-dark mb-2">100% Secure &amp; Private
                    </h3>

                    <p class="text-secondary lh-lg mb-0">
                        Files auto-deleted after 12 days. UPI payments via
                    secure gateway. Your data never shared.
                    </p>

                </div>

            </div>


            <!-- Delivery -->
            <div class="col-12 col-md-4">

                <div class="trust-card trust-blue h-100">

                    <div class="trust-icon icon-blue mb-4">
                        <i class="bi bi-truck"></i>
                    </div>

                    <h3 class="h5 fw-bold text-dark mb-2">3-5 Day Delivery
                    </h3>

                    <p class="text-secondary lh-lg mb-0">
                        Speed Post &amp; DTDC across all 23 districts.
                    Tracking provided for every order.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="mb-5 container">

        <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border">

            <div class="row align-items-center g-5">

                <!-- Map -->
                <div class="col-12 col-lg-5 text-center">

                    <div class="position-relative d-inline-block">

                        <img
                            src="https://www.seekpng.com/png/small/299-2994424_west-bengal-west-bengal-map-vector.png"
                            alt="West Bengal Delivery Map"
                            class="delivery-map img-fluid">

                        <span class="delivery-badge">ALL DISTRICTS
                        </span>

                    </div>

                </div>


                <!-- Content -->
                <div class="col-12 col-lg-7">

                    <h2 class="display-6 fw-bold text-dark mb-3">Delivery to
                    <span class="text-primary">Every Corner
                    </span>
                        of Bengal
                    </h2>

                    <p class="text-secondary fs-5 lh-lg mb-4">
                        From Darjeeling hills to Sundarbans delta, Kolkata to
                    Jhargram — we deliver to 23 districts, 341 blocks,
                    and 40,000+ villages.
                    </p>


                    <!-- Districts -->
                    <div class="row row-cols-2 row-cols-sm-3 g-2">

                        <div class="col">
                            <div class="district-box">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Kolkata</span>
                            </div>
                        </div>

                        <div class="col">
                            <div class="district-box">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Murshidabad</span>
                            </div>
                        </div>

                        <div class="col">
                            <div class="district-box">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>North 24 Pgs</span>
                            </div>
                        </div>

                        <div class="col">
                            <div class="district-box">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Howrah</span>
                            </div>
                        </div>

                        <div class="col">
                            <div class="district-box">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Malda</span>
                            </div>
                        </div>

                        <div class="col">
                            <div class="district-box district-more">
                                <i class="bi bi-plus-lg"></i>
                                <span>18 More</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Official Card Section -->
    <div class="mb-5 pb-4 container">

        <!-- Section Heading -->
        <div class="text-center mb-4">
            <h2 class="display-5 fw-bold text-dark mb-2">Print Any Official Card
            </h2>

            <p class="text-secondary mb-0">
                All formats supported with perfect color matching
            </p>
        </div>

        <!-- Cards -->
        <div class="row row-cols-2 row-cols-lg-4 g-4">

            <!-- Aadhaar Card -->
            <div class="col">
                <div class="official-card bg-white rounded-4 p-4 text-center h-100">

                    <div class="official-icon icon-aadhaar mx-auto mb-3">
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    <h3 class="fw-bold fs-5 text-dark mb-1">Aadhaar Card
                    </h3>

                    <p class="small text-secondary mb-0">
                        UIDAI Standard
                    </p>

                    <div class="badge rounded-pill text-success bg-success-subtle mt-3 px-3 py-2">
                        <i class="fa-solid fa-check me-1"></i>
                        Most Popular
                    </div>

                </div>
            </div>


            <!-- PAN Card -->
            <div class="col">
                <div class="official-card bg-white rounded-4 p-4 text-center h-100">

                    <div class="official-icon icon-pan mx-auto mb-3">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>

                    <h3 class="fw-bold fs-5 text-dark mb-1">PAN Card
                    </h3>

                    <p class="small text-secondary mb-0">
                        NSDL / UTI
                    </p>

                    <div class="badge rounded-pill text-primary bg-primary-subtle mt-3 px-3 py-2">
                        <i class="fa-solid fa-bolt me-1"></i>
                        HD Print
                    </div>

                </div>
            </div>


            <!-- E-Ration Card -->
            <div class="col">
                <div class="official-card bg-white rounded-4 p-4 text-center h-100">

                    <div class="official-icon icon-ration mx-auto mb-3">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>

                    <h3 class="fw-bold fs-5 text-dark mb-1">E-Ration Card
                    </h3>

                    <p class="small text-secondary mb-0">
                        Food &amp; Supplies
                    </p>

                    <div class="badge rounded-pill text-success bg-success-subtle mt-3 px-3 py-2">
                        <i class="fa-solid fa-shield me-1"></i>
                        Durable
                    </div>

                </div>
            </div>


            <!-- Voter ID -->
            <div class="col">
                <div class="official-card bg-white rounded-4 p-4 text-center h-100">

                    <div class="official-icon icon-voter mx-auto mb-3">
                        <i class="fa-solid fa-person-booth"></i>
                    </div>

                    <h3 class="fw-bold fs-5 text-dark mb-1">Voter ID
                    </h3>

                    <p class="small text-secondary mb-0">
                        EPIC Card
                    </p>

                    <div class="badge rounded-pill text-purple bg-purple-subtle mt-3 px-3 py-2">
                        <i class="fa-solid fa-certificate me-1"></i>
                        Official
                    </div>

                </div>
            </div>

        </div>
    </div>


   
    <!-- Order in 3 Simple Steps -->
    <div class="order-steps-section rounded-4 p-4 p-md-5 mb-5 container">

        <h2 class="text-center fw-bold text-dark mb-5">Order in 3 Simple Steps
        </h2>

        <div class="row g-4 justify-content-center">

            <!-- Step 1 -->
            <div class="col-12 col-md-4 text-center">
                <div class="step-number mx-auto mb-3">
                    1
                </div>

                <h3 class="fw-bold fs-5 mb-2">Upload PDF
                </h3>

                <p class="text-secondary small mb-0">
                    Select your card PDF files from phone or computer
                </p>
            </div>


            <!-- Step 2 -->
            <div class="col-12 col-md-4 text-center">
                <div class="step-number mx-auto mb-3">
                    2
                </div>

                <h3 class="fw-bold fs-5 mb-2">Pay Securely
                </h3>

                <p class="text-secondary small mb-0">
                    UPI, Paytm, PhonePe — instant confirmation
                </p>
            </div>


            <!-- Step 3 -->
            <div class="col-12 col-md-4 text-center">
                <div class="step-number mx-auto mb-3">
                    3
                </div>

                <h3 class="fw-bold fs-5 mb-2">Get Delivery
                </h3>

                <p class="text-secondary small mb-0">
                    Printed in 24hrs, delivered in 3-5 days
                </p>
            </div>

        </div>
    </div>


    


    @if (filled(config('services.whatsapp_contact_url')))
        <div class="whatsapp-floating">
            <a href="{{ config('services.whatsapp_contact_url') }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Contact Bengal PVC on WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
