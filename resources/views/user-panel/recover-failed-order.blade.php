@extends('user-panel.layout.app')

@section('content')

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-arrow-repeat text-primary me-2"></i>
                                Why this option?
                            </h5>

                            <ul class="mb-4 ps-3">

                                <li class="mb-3">
                                    <strong>Payment succeeded but order didn't show in your history?</strong>
                                    This is usually due to a network interruption while saving files.
                                </li>

                                <li class="mb-3">If you have an <strong>Order ID starting with PVC</strong> from
                the payment gateway and the payment was verified, you can recover
                your order here.
                                </li>

                                <li class="mb-3">The <strong>amount will be auto-fetched</strong> from our payment
                records. You only need to re-upload the files and confirm your address.
                                </li>

                                <li class="mb-3">Your order will appear in <strong>Order History</strong> after
                successful submission.
                                </li>

                                <li>If you don't have an Order ID, contact support with your
                <strong>payment screenshot on WhatsApp</strong>.
                                </li>

                            </ul>

                            <div class="alert alert-light border rounded-3 mb-4">

                                <i class="bi bi-info-circle-fill text-primary me-2"></i>

                                <strong>Example Order ID:</strong>
                                <span class="text-primary">PVC1714200000000</span>

                            </div>


                            <!-- Failed Order ID -->

                            <label class="form-label fw-semibold">
                                Enter Failed Order ID
                            </label>

                            <div class="row g-2">

                                <div class="col-md-9">

                                    <div class="input-group">

                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-receipt"></i>
                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            
                                            placeholder="Enter PVC Order ID">
                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <button type="button"
                                        class="btn btn-primary w-100"
                                        >

                                        <i class="bi bi-search me-1"></i>
                                        Fetch

                                    </button>

                                </div>

                            </div>

                            <div id="fetchMessage" class="mt-3"></div>

                        </div>
                    </div>
                
@endsection
