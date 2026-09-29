@extends('user-panel.layout.app')

@section('content')


                    <div class="main-card">

                        <!-- HEADER -->
                        <div class="mb-4">
                            <h2 class="page-title">
                                <i class="bi bi-images me-2 text-primary"></i>
                                Custom Photo Print
                            </h2>
                            <hr />
                        </div>


                        <!-- PRICING -->
                        <h4 class="section-title">Photo Print Pricing</h4>

                        <div class="row g-3 mb-4">

                            <div class="col-md-6">
                                <div class="price-card">

                                    <div class="d-flex align-items-start gap-3">

                                        <div class="price-icon">
                                            <i class="bi bi-image"></i>
                                        </div>

                                        <div>
                                            <h5 class="fw-bold mb-1">4x6 Inch (Standard)</h5>

                                            <p class="text-muted small mb-3">
                                                Lab Quality • Glossy • 300 GSM
                                            </p>

                                            <div class="price">120 / photo</div>

                                            <small class="text-muted">Shipping: 50
                                            </small>
                                        </div>

                                    </div>

                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="price-card">

                                    <div class="d-flex align-items-start gap-3">

                                        <div class="price-icon">
                                            <i class="bi bi-file-earmark-image"></i>
                                        </div>

                                        <div>
                                            <h5 class="fw-bold mb-1">A4 Size (8x12)</h5>

                                            <p class="text-muted small mb-3">
                                                Premium Photo Print • Large Format
                                            </p>

                                            <div class="price">240 / photo</div>

                                            <small class="text-muted">Shipping: 50
                                            </small>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>


                        <!-- SELECT SIZE -->
                        <div class="mb-4">

                            <h4 class="section-title">Select Photo Size</h4>

                            <div class="row g-3">

                                <div class="col-6">

                                    <label class="size-option w-100">

                                        <input type="radio"
                                            name="photoSize"
                                            value="4x6"
                                            checked
                                            onchange="calculateTotal()">

                                        <div class="size-box">
                                            <i class="bi bi-image"></i>
                                            4x6 Inch
                            <small class="d-block text-muted mt-1">120 / photo
                            </small>
                                        </div>

                                    </label>

                                </div>


                                <div class="col-6">

                                    <label class="size-option w-100">

                                        <input type="radio"
                                            name="photoSize"
                                            value="a4"
                                            onchange="calculateTotal()">

                                        <div class="size-box">
                                            <i class="bi bi-file-earmark-image"></i>
                                            A4 Size
                            <small class="d-block text-muted mt-1">240 / photo
                            </small>
                                        </div>

                                    </label>

                                </div>

                            </div>

                        </div>


                        <!-- PHOTO UPLOAD -->
                        <div class="mb-4">

                            <h4 class="section-title">Upload Photos</h4>

                            <label for="photoUpload" class="upload-box w-100">

                                <i class="bi bi-cloud-arrow-up-fill"></i>

                                <h5 class="fw-bold mt-3 mb-1">Click or Drag Photos here
                                </h5>

                                <p class="text-muted mb-0">
                                    JPG, PNG accepted • High resolution recommended • Max 80MB total
                                </p>

                            </label>

                            <input type="file"
                                id="photoUpload"
                                class="d-none"
                                accept=".jpg,.jpeg,.png"
                                multiple
                                onchange="calculateTotal()">
                        </div>


                        <!-- DRIVE -->
                        <div class="inner-box mb-4">

                            <h4 class="section-title">
                                <i class="bi bi-google me-2"></i>
                                Or Add Google Drive Photo Links
                            </h4>

                            <p class="text-muted">
                                Paste Drive links for photos. Mix uploads + links allowed.
                            </p>

                            <div id="driveLinks">

                                <div class="input-group mb-2">

                                    <input type="url"
                                        class="form-control drive-link"
                                        placeholder="https://drive.google.com/..."
                                        oninput="calculateTotal()">

                                    <button type="button"
                                        class="btn btn-outline-primary"
                                        onclick="addDriveLink()">

                                        <i class="bi bi-plus-lg"></i>
                                        Add

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- ADDRESS -->
                        <div class="inner-box mb-4">

                            <div class="d-flex flex-column flex-sm-row justify-content-between
                        align-items-sm-center gap-2 mb-3">

                                <h4 class="section-title mb-0">
                                    <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                                    Delivery Address (for Photos)
                                </h4>

                                <button type="button"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Load Saved Address
                                </button>

                            </div>


                            <div class="row g-3">

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Village / Area / Street">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Landmark (Optional)">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Post Office">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Police Station">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="PIN Code"
                                        maxlength="6">
                                </div>

                                <div class="col-md-6">

                                    <select class="form-select">
                                        <option value="">Select District</option>
                                        <option>Kolkata</option>
                                        <option>Howrah</option>
                                        <option>Hooghly</option>
                                        <option>North 24 Parganas</option>
                                        <option>South 24 Parganas</option>
                                        <option>Purba Medinipur</option>
                                        <option>Paschim Medinipur</option>
                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- SUMMARY -->
                        <div class="summary-card">

                            <h4 class="section-title">Order Summary
                            </h4>

                            <div class="summary-row">
                                <span>Total Photos:</span>
                                <strong id="totalPhotos">0</strong>
                            </div>

                            <div class="summary-row">
                                <span>Rate:</span>
                                <strong id="rateText">120 / photo 4x6</strong>
                            </div>

                            <div class="summary-row">
                                <span>Shipping:</span>
                                <strong id="shippingText">0</strong>
                            </div>


                            <div class="total-area d-flex justify-content-between align-items-end">

                                <div class="total-label">
                                    TOTAL PAYABLE
                                </div>

                                <div class="total-price" id="totalPrice">
                                    0
                                </div>

                            </div>


                            <div class="d-grid mt-4">

                                <button type="button" class="pay-btn">

                                    <i class="bi bi-shield-check me-2"></i>

                                    PAY &amp; ORDER PHOTOS

                                </button>

                            </div>

                        </div>

                    </div>

                
@endsection
