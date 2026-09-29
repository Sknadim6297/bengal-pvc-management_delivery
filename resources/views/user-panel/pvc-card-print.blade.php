@extends('user-panel.layout.app')

@section('brand-label', 'Indias')

@section('content')


                    <form id="pvcCardPrintForm" class="page-card" method="POST" action="{{ route('user.pvc-card-print.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ $submissionKey }}">

                        <div class="mb-4">
                            <h2 class="section-title mb-2"><i class="bi bi-filetype-pdf text-danger"></i>Upload PDF for Printing</h2>
                            <hr />
                        </div>

                        @if (session('statusMessage'))
                            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif


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
                                            <td><strong>₹{{ $pricing->normal_single_price }}</strong></td>
                                        </tr>

                                        <tr>
                                            <td>2 - 5 Cards</td>
                                            <td class="text-success fw-bold">{{ $pricing->normal_2_5_discount }}% OFF</td>
                                            <td><strong>₹{{ $pricing->normal_2_5_price }}</strong></td>
                                        </tr>

                                        <tr>
                                            <td>6 - 7 Cards</td>
                                            <td class="text-success fw-bold">{{ $pricing->normal_6_7_discount }}% OFF</td>
                                            <td><strong>₹{{ $pricing->normal_6_7_price }}</strong></td>
                                        </tr>

                                        <tr>
                                            <td>8 - 10 Cards</td>
                                            <td class="text-success fw-bold">{{ $pricing->normal_8_10_discount }}% OFF</td>
                                            <td><strong>₹{{ $pricing->normal_8_10_price }}</strong></td>
                                        </tr>

                                        <tr>
                                            <td>11+ Cards</td>
                                            <td class="text-success fw-bold">{{ $pricing->normal_11_plus_discount }}% OFF</td>
                                            <td><strong>₹{{ $pricing->normal_11_plus_price }}</strong></td>
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
                                            <td><strong>₹{{ $pricing->premium_under_12_price }}</strong> (No Discount)</td>
                                        </tr>

                                        <tr>
                                            <td>12+ Cards</td>
                                            <td><strong>₹{{ $pricing->premium_12_plus_price }}</strong> (No Discount)</td>
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
                                    ₹{{ $pricing->shipping_fee }} Flat — <span class="text-success fw-bold">Free on {{ $pricing->free_shipping_minimum_quantity }}+ cards</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-box h-100">
                                    <strong>Card Cover</strong><br>
                                    Custom Printed Cover — ₹{{ $pricing->card_cover_price }}/card
                                </div>
                            </div>

                        </div>


                        <div class="alert alert-success">
                            <i class="bi bi-truck me-2"></i>
                            <strong>FREE Delivery for {{ $pricing->free_shipping_minimum_quantity }}+ cards!</strong>
                            No discounts on Premium quality.
                        </div>


                        <!-- Quality -->
                        <div class="mt-4">

                            <h4 class="sub-title">Select Print Quality</h4>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <label class="quality-card active d-block">

                                        <input class="form-check-input float-end" type="radio" name="print_quality" value="normal" checked>

                                        <div class="d-flex justify-content-between align-items-start mb-3">

                                            <h5 class="fw-bold mb-0">Normal Quality
                                            </h5>

                                            <span class="badge badge-standard">STANDARD
                                            </span>

                                        </div>

                                        <p class="text-muted">
                                            800 Micron PVC • Bulk discounts + coupons
                                        </p>

                                        <h4 class="fw-bold text-primary mb-0">₹{{ $pricing->normal_single_price }} / card
                                        </h4>

                                    </label>

                                </div>


                                <div class="col-md-6">

                                    <label class="quality-card d-block">

                                        <input class="form-check-input float-end" type="radio" name="print_quality" value="premium">

                                        <div class="d-flex justify-content-between align-items-start mb-3">

                                            <h5 class="fw-bold mb-0">Premium Quality
                                            </h5>

                                            <span class="badge badge-premium">PREMIUM
                                            </span>

                                        </div>

                                        <p class="text-muted">
                                            800 Micron Premium PVC • Super Gloss • No discount • Long lasting
                                        </p>

                                        <h6 class="fw-bold text-warning mb-0">₹{{ $pricing->premium_under_12_price }} (&lt;12) / ₹{{ $pricing->premium_12_plus_price }} (12+)
                                        </h6>

                                    </label>

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
                                            id="cardCover"
                                            name="include_card_cover"
                                            value="1">

                                        <label class="form-check-label fw-bold" for="cardCover">
                                            Custom Printed Card Cover — ₹{{ $pricing->card_cover_price }}/card
                                        </label>

                                    </div>

                                    <p class="text-muted mb-0 mt-2">
                                        Own Shop Branded PVC cover with custom print • ₹{{ $pricing->card_cover_price }} per card
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
                                name="pdf_files[]"
                                accept=".pdf,application/pdf"
                                multiple>

                            <div id="pdfFileList" class="list-group mt-3"></div>
                            <div id="uploadFeedback" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        </div>


                        <!-- Google Drive -->
                        <div class="drive-box mt-4">

                            <h4 class="sub-title">Or Add Google Drive Links
                <small class="text-muted">(Optional)</small>
                            </h4>

                            <p class="text-muted">
                                Paste shareable Drive links for PDFs. You can mix uploads + Drive links.
                Total pcs = Files + Links: <strong id="totalPieces" aria-live="polite">0</strong>.
                            </p>

                            <div id="driveLinks">

                                <div class="input-group mb-2">

                                    <span class="input-group-text">
                                        <i class="bi bi-google"></i>
                                    </span>

                                    <input type="url"
                                        class="form-control"
                                        id="driveLinkInput"
                                        placeholder="https://drive.google.com/...">
                                </div>

                            </div>

                            <div id="driveLinkList" class="mt-2"></div>
                            <div id="driveLinkFields"></div>

                            <button type="button"
                                class="btn btn-outline-primary mt-2"
                                onclick="addDriveLink()">

                                <i class="bi bi-plus-circle me-1"></i>
                                Add

                            </button>

                        </div>


                        <!-- Submit -->
                        <div class="d-grid d-md-flex justify-content-md-end mt-4">

                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                Continue
                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </form>

                
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('pvcCardPrintForm');
            const fileInput = document.getElementById('pdfUpload');
            const fileList = document.getElementById('pdfFileList');
            const driveInput = document.getElementById('driveLinkInput');
            const driveList = document.getElementById('driveLinkList');
            const driveFields = document.getElementById('driveLinkFields');
            const totalPieces = document.getElementById('totalPieces');
            const feedback = document.getElementById('uploadFeedback');
            const maxPdfBytes = 80 * 1024 * 1024;
            const selectedFiles = [];
            const selectedDriveLinks = [];

            function showFeedback(message) {
                feedback.textContent = message;
                feedback.classList.toggle('d-none', !message);
            }

            function fileKey(file) {
                return `${file.name}:${file.size}:${file.lastModified}`;
            }

            function totalPdfBytes() {
                return selectedFiles.reduce((total, file) => total + file.size, 0);
            }

            function renderFiles() {
                const transfer = new DataTransfer();
                selectedFiles.forEach((file) => transfer.items.add(file));
                fileInput.files = transfer.files;
                fileList.replaceChildren();

                selectedFiles.forEach((file, index) => {
                    const row = document.createElement('div');
                    row.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';

                    const details = document.createElement('span');
                    details.className = 'text-break';
                    details.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                    removeButton.setAttribute('aria-label', `Remove ${file.name}`);
                    removeButton.textContent = 'Remove';
                    removeButton.addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        renderFiles();
                        updateTotal();
                    });

                    row.append(details, removeButton);
                    fileList.append(row);
                });
            }

            function updateTotal() {
                totalPieces.textContent = String(selectedFiles.length + selectedDriveLinks.length);
            }

            function addFiles(files) {
                let rejectedType = false;
                let rejectedSize = false;

                Array.from(files).forEach((file) => {
                    if (!/\.pdf$/i.test(file.name)) {
                        rejectedType = true;
                        return;
                    }

                    if (selectedFiles.some((selectedFile) => fileKey(selectedFile) === fileKey(file))) {
                        return;
                    }

                    if (totalPdfBytes() + file.size > maxPdfBytes) {
                        rejectedSize = true;
                        return;
                    }

                    selectedFiles.push(file);
                });

                renderFiles();
                updateTotal();
                showFeedback(rejectedType
                    ? 'Only PDF files can be added.'
                    : rejectedSize ? 'PDF files must total no more than 80 MB.' : '');
            }

            function normalizeDriveLink(value) {
                try {
                    const url = new URL(value.trim());
                    const allowedHosts = ['drive.google.com', 'docs.google.com'];

                    if (url.protocol !== 'https:' || !allowedHosts.includes(url.hostname.toLowerCase())) {
                        return null;
                    }

                    const hasDriveResourcePath = url.hostname === 'drive.google.com'
                        && /^\/file\/d\/[^/]+(?:\/|$)/.test(url.pathname);
                    const hasDriveResourceId = url.hostname === 'drive.google.com'
                        && ['/open', '/uc'].includes(url.pathname)
                        && url.searchParams.has('id');
                    const hasDocsResourcePath = url.hostname === 'docs.google.com'
                        && /^\/(?:document|spreadsheets|presentation|forms)\/d\/[^/]+(?:\/|$)/.test(url.pathname);

                    if (!(hasDriveResourcePath || hasDriveResourceId || hasDocsResourcePath)) {
                        return null;
                    }

                    url.hash = '';
                    return url.toString();
                } catch {
                    return null;
                }
            }

            function renderDriveLinks() {
                driveList.replaceChildren();
                driveFields.replaceChildren();

                selectedDriveLinks.forEach((url, index) => {
                    const row = document.createElement('div');
                    row.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';

                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.className = 'text-break';
                    link.textContent = url;

                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'drive_links[]';
                    hiddenField.value = url;
                    driveFields.append(hiddenField);

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                    removeButton.setAttribute('aria-label', 'Remove Google Drive link');
                    removeButton.textContent = 'Remove';
                    removeButton.addEventListener('click', () => {
                        selectedDriveLinks.splice(index, 1);
                        renderDriveLinks();
                        updateTotal();
                    });

                    row.append(link, removeButton);
                    driveList.append(row);
                });
            }

            window.addDriveLink = function addDriveLink() {
                const normalizedLink = normalizeDriveLink(driveInput.value);

                if (!driveInput.value.trim()) {
                    showFeedback('Enter a Google Drive or Google Docs share link.');
                    return;
                }

                if (!normalizedLink) {
                    showFeedback('Enter a valid HTTPS Google Drive or Google Docs share link.');
                    return;
                }

                if (selectedDriveLinks.includes(normalizedLink)) {
                    showFeedback('That Google Drive link has already been added.');
                    return;
                }

                selectedDriveLinks.push(normalizedLink);
                driveInput.value = '';
                renderDriveLinks();
                updateTotal();
                showFeedback('');
            };

            fileInput.addEventListener('change', (event) => addFiles(event.target.files));
            document.querySelector('.upload-box[for="pdfUpload"]').addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
            });
            document.querySelector('.upload-box[for="pdfUpload"]').addEventListener('drop', (event) => {
                event.preventDefault();
                addFiles(event.dataTransfer.files);
            });
            driveInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    window.addDriveLink();
                }
            });
            form.addEventListener('submit', (event) => {
                if (selectedFiles.length === 0 && selectedDriveLinks.length === 0) {
                    event.preventDefault();
                    showFeedback('Add at least one PDF file or Google Drive link.');
                }
            });

            document.querySelectorAll('input[name="print_quality"]').forEach((qualityOption) => {
                qualityOption.addEventListener('change', () => {
                    document.querySelectorAll('.quality-card').forEach((card) => card.classList.remove('active'));
                    qualityOption.closest('.quality-card').classList.add('active');
                });
            });
        })();
    </script>
@endpush
