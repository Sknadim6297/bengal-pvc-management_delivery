@extends('user-panel.layout.app')

@section('content')


                    <div class="support-card">

                        <div class="row g-4">
                            <div class="mb-2">
                                <h2 class="page-title">Support & Tracking
                                </h2>
                                <hr />
                            </div>
                            <!-- TRACK PARCEL -->
                            <div class="col-lg-6">

                                <div class="support-box tracking-box h-100">

                                    <div class="support-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                    <h4>Track Parcel Status</h4>

                                    <p class="text-muted">
                                        Enter your Order ID (e.g., EP1234567) to see live updates.
                                    </p>

                                    <div class="row g-2">

                                        <div class="col-sm-8">

                                            <input type="text"
                                                class="form-control"
                                                id="orderId"
                                                placeholder="Enter Order ID">
                                        </div>

                                        <div class="col-sm-4">

                                            <button type="button"
                                                class="btn btn-primary w-100"
                                                onclick="trackOrder()">

                                                <i class="bi bi-search me-1"></i>
                                                Track

                                            </button>

                                        </div>

                                    </div>

                                    <div id="trackingResult" class="mt-3"></div>

                                </div>

                            </div>


                            <!-- SUPPORT -->
                            <div class="col-lg-6">

                                <div class="support-box whatsapp-box h-100">

                                    <div class="support-icon whatsapp-icon">
                                        <i class="bi bi-whatsapp"></i>
                                    </div>

                                    <h4>24/7 Priority Support</h4>

                                    <p class="text-muted">
                                        Facing issues or need custom bulk discounts?
                    Chat with us instantly.
                                    </p>

                                    <a href="#"
                                        class="btn btn-successd-flex justify-content-center align-items-center bg-success text-white">

                                        <i class="bi bi-whatsapp me-2"></i>
                                        Open WhatsApp

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                
@endsection
