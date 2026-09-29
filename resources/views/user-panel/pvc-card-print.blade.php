@extends('user-panel.layout.app')

@section('brand-label', 'Indias')

@section('content')


                    <div class="page-card">

                        <div class="mb-4">
                            <h2 class="section-title mb-2"><i class="bi bi-filetype-pdf text-danger"></i>Upload PDF for Printing</h2>
                            <hr />
                        </div>


                        <!-- Pricing -->
                        <div class="mb-4">

                            <h4 class="sub-title"><i class="bi bi-tags-fill me-2"></i>Pricing &amp; Bulk Discounts</h4>

                            <div class="table-responsive">

                                <table class="table table-bordered price-table align-middle">

                                    <thead>
                                        <tr>
                                            <th>Quantity</th>
                                            <th>Discount</th>
                                            <th>Effective Price / Card</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1 Card</td>
                                            <td>None</td>
                                            <td><strong>80</strong></td>
                                        </tr>

                                        <tr>
                                            <td>2 - 5 Cards</td>
                                            <td class="text-success fw-bold">31% OFF</td>
                                            <td><strong>55</strong></td>
                                        </tr>

                                        <tr>
                                            <td>6 - 7 Cards</td>
                                            <td class="text-success fw-bold">40% OFF</td>
                                            <td><strong>48</strong></td>
                                        </tr>

                                        <tr>
                                            <td>8 - 10 Cards</td>
                                            <td class="text-success fw-bold">48% OFF</td>
                                            <td><strong>42</strong></td>
                                        </tr>

                                        <tr>
                                            <td>11+ Cards</td>
                                            <td class="text-success fw-bold">54% OFF</td>
                                            <td><strong>37</strong></td>
                                        </tr>
                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- Premium -->
                        <div class="mb-4">

                            <h4 class="sub-title"><i class="bi bi-tags-fill me-2"></i>Premium Quality</h4>

                            <div class="table-responsive">

                                <table class="table table-bordered price-table">

                                    <thead>
                                        <tr>
                                            <th>Quantity</th>
                                            <th>Rate / Card</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1 - 11 Cards</td>
                                            <td><strong>90</strong> (No Discount)</td>
                                        </tr>

                                        <tr>
                                            <td>12+ Cards</td>
                                            <td><strong>50</strong> (No Discount)</td>
                                        </tr>
                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- Extra -->
                        <div class="row g-3 mb-4">

                            <div class="col-md-6">
                                <div class="info-box h-100">
                                    <strong>Shipping</strong><br>
                                    40 Flat — <span class="text-success fw-bold">Free on 10+ cards</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-box h-100">
                                    <strong>Card Cover</strong><br>
                                    Custom Printed Cover — 15/card
                                </div>
                            </div>

                        </div>


                        <div class="alert alert-success">
                            <i class="bi bi-truck me-2"></i>
                            <strong>FREE Delivery for 10+ cards!</strong>
                            No discounts on Premium quality.
                        </div>


                        <!-- Quality -->
                        <div class="mt-4">

                            <h4 class="sub-title">Select Print Quality</h4>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <div class="quality-card active">

                                        <div class="d-flex justify-content-between align-items-start mb-3">

                                            <h5 class="fw-bold mb-0">Normal Quality
                                            </h5>

                                            <span class="badge badge-standard">STANDARD
                                            </span>

                                        </div>

                                        <p class="text-muted">
                                            800 Micron PVC • Bulk discounts + coupons
                                        </p>

                                        <h4 class="fw-bold text-primary mb-0">80 / card
                                        </h4>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="quality-card">

                                        <div class="d-flex justify-content-between align-items-start mb-3">

                                            <h5 class="fw-bold mb-0">Premium Quality
                                            </h5>

                                            <span class="badge badge-premium">PREMIUM
                                            </span>

                                        </div>

                                        <p class="text-muted">
                                            800 Micron Premium PVC • Super Gloss • No discount • Long lasting
                                        </p>

                                        <h6 class="fw-bold text-warning mb-0">90 (&lt;12) / 50 (12+)
                                        </h6>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Card Cover -->
                        <div class="mt-4">

                            <div class="card border-0 bg-light">

                                <div class="card-body">

                                    <div class="form-check">

                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="cardCover">

                                        <label class="form-check-label fw-bold" for="cardCover">
                                            Custom Printed Card Cover
                                        </label>

                                    </div>

                                    <p class="text-muted mb-0 mt-2">
                                        Own Shop Branded PVC cover with custom print • 15 per card
                                    </p>

                                </div>

                            </div>

                        </div>


                        <!-- File Upload -->
                        <div class="mt-4">

                            <h4 class="sub-title">Upload PDF Files</h4>

                            <label class="upload-box w-100" for="pdfUpload">

                                <i class="bi bi-cloud-arrow-up-fill"></i>

                                <h5 class="mt-3 mb-1">Click or Drag PDFs here
                                </h5>

                                <p class="text-muted mb-0">
                                    Ensure clear quality. Only .pdf accepted (Max 80MB total).
                                </p>

                            </label>

                            <input type="file"
                                class="d-none"
                                id="pdfUpload"
                                accept=".pdf"
                                multiple>
                        </div>


                        <!-- Google Drive -->
                        <div class="drive-box mt-4">

                            <h4 class="sub-title">Or Add Google Drive Links
                <small class="text-muted">(Optional)</small>
                            </h4>

                            <p class="text-muted">
                                Paste shareable Drive links for PDFs. You can mix uploads + Drive links.
                Total pcs = Files + Links.
                            </p>

                            <div id="driveLinks">

                                <div class="input-group mb-2">

                                    <span class="input-group-text">
                                        <i class="bi bi-google"></i>
                                    </span>

                                    <input type="url"
                                        class="form-control"
                                        placeholder="https://drive.google.com/...">
                                </div>

                            </div>

                            <button type="button"
                                class="btn btn-outline-primary mt-2"
                                onclick="addDriveLink()">

                                <i class="bi bi-plus-circle me-1"></i>
                                Add

                            </button>

                        </div>


                        <!-- Submit -->
                        <div class="d-grid d-md-flex justify-content-md-end mt-4">

                            <button class="btn btn-primary btn-lg px-5">
                                Continue
                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </div>

                
@endsection
