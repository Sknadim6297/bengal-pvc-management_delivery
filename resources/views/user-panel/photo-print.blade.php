@extends('user-panel.layout.app')

@section('content')


                    <form id="photoPrintForm" class="main-card" method="POST" action="{{ route('user.photo-print.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ $submissionKey }}">

                        <!-- HEADER -->
                        <div class="mb-4">
                            <h2 class="page-title">
                                <i class="bi bi-images me-2 text-primary"></i>
                                Custom Photo Print
                            </h2>
                            <hr />
                        </div>

                        @if (session('statusMessage'))
                            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif


                        <!-- PRICING -->
                        <h4 class="section-title">Photo Print Pricing</h4>

                        <div class="row g-3 mb-4">
                            @forelse ($photoOptions as $option)
                                <div class="col-md-6">
                                    <div class="price-card">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="price-icon">
                                                <i class="bi {{ str_contains($option['slug'], '4x6') ? 'bi-image' : 'bi-file-earmark-image' }}"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-1">{{ $option['name'] }}</h5>
                                                <p class="text-muted small mb-3">{{ $option['description'] }}</p>
                                                <div class="price">₹{{ number_format((float) $option['unit_price'], 2) }} / photo</div>
                                                <small class="text-muted">Shipping: ₹{{ number_format((float) $shippingFee, 2) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12"><div class="text-muted">Photo sizes are temporarily unavailable.</div></div>
                            @endforelse
                        </div>


                        <!-- SELECT SIZE -->
                        <div class="mb-4">

                            <h4 class="section-title">Select Photo Size</h4>

                            <div class="row g-3">
                                @foreach ($photoOptions as $option)
                                    <div class="col-6">
                                        <label class="size-option w-100">
                                            <input type="radio" name="photo_option_id" value="{{ $option['id'] }}" data-slug="{{ $option['slug'] }}" data-rate="{{ $option['unit_price'] }}" @checked($loop->first)>
                                            <div class="size-box">
                                                <i class="bi {{ str_contains($option['slug'], '4x6') ? 'bi-image' : 'bi-file-earmark-image' }}"></i>
                                                {{ $option['name'] }}
                                                <small class="d-block text-muted mt-1">₹{{ number_format((float) $option['unit_price'], 2) }} / photo</small>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
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
                                name="photo_files[]"
                                accept=".jpg,.jpeg,.png"
                                multiple>
                            <div id="photoFileList" class="list-group mt-3"></div>
                            <div id="photoFeedback" class="alert alert-danger mt-3 d-none" role="alert"></div>
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
                                        id="photoDriveLinkInput"
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

                            <div id="photoDriveLinkList" class="mt-2"></div>
                            <div id="photoDriveLinkFields"></div>

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
                                        name="delivery_address[village_area]"
                                        placeholder="Village / Area / Street">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        name="delivery_address[landmark]"
                                        placeholder="Landmark (Optional)">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        name="delivery_address[post_office]"
                                        placeholder="Post Office">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        name="delivery_address[police_station]"
                                        placeholder="Police Station">
                                </div>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control"
                                        name="delivery_address[pincode]"
                                        placeholder="PIN Code"
                                        maxlength="6">
                                </div>

                                <div class="col-md-6">

                                    <select class="form-select" name="delivery_district" required>
                                        <option value="">Select District</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district }}" @selected(old('delivery_district') === $district)>{{ $district }}</option>
                                        @endforeach
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
                                <strong id="rateText">{{ $photoOptions->isNotEmpty() ? '₹'.number_format((float) $photoOptions->first()['unit_price'], 2).' / photo '.$photoOptions->first()['name'] : 'Unavailable' }}</strong>
                            </div>

                            <div class="summary-row">
                                <span>Shipping:</span>
                                <strong id="shippingText">₹0</strong>
                            </div>


                            <div class="total-area d-flex justify-content-between align-items-end">

                                <div class="total-label">
                                    TOTAL PAYABLE
                                </div>

                                <div class="total-price" id="totalPrice">
                                    ₹0
                                </div>

                            </div>


                            <div class="d-grid mt-4">

                                <button type="submit" class="pay-btn" @disabled($photoOptions->isEmpty())>

                                    <i class="bi bi-shield-check me-2"></i>

                                    PAY &amp; ORDER PHOTOS

                                </button>

                            </div>

                        </div>

                    </form>

                
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('photoPrintForm');
            const fileInput = document.getElementById('photoUpload');
            const fileList = document.getElementById('photoFileList');
            const driveInput = document.getElementById('photoDriveLinkInput');
            const driveList = document.getElementById('photoDriveLinkList');
            const driveFields = document.getElementById('photoDriveLinkFields');
            const feedback = document.getElementById('photoFeedback');
            const options = @json($photoOptions);
            const shippingFee = Number(@json($shippingFee));
            const selectedFiles = [];
            const selectedDriveLinks = [];
            const maximumBytes = 80 * 1024 * 1024;

            function showFeedback(message) {
                feedback.textContent = message;
                feedback.classList.toggle('d-none', !message);
            }

            function fileKey(file) {
                return `${file.name}:${file.size}:${file.lastModified}`;
            }

            function totalFileBytes() {
                return selectedFiles.reduce((total, file) => total + file.size, 0);
            }

            function updateCountAndPreview() {
                const quantity = selectedFiles.length + selectedDriveLinks.length;
                const selectedOption = options.find((option) => String(option.id) === form.querySelector('input[name="photo_option_id"]:checked')?.value);
                const totalPhotos = document.getElementById('totalPhotos');
                const rateText = document.getElementById('rateText');
                const shippingText = document.getElementById('shippingText');
                const totalPrice = document.getElementById('totalPrice');

                totalPhotos.textContent = String(quantity);
                rateText.textContent = selectedOption ? `₹${Number(selectedOption.unit_price).toFixed(2)} / photo ${selectedOption.name}` : 'Unavailable';
                const shipping = quantity > 0 ? shippingFee : 0;
                shippingText.textContent = `₹${shipping.toFixed(2)}`;
                const previewTotal = selectedOption && quantity > 0 ? Number(selectedOption.unit_price) * quantity + shipping : 0;
                totalPrice.textContent = `₹${previewTotal.toFixed(2)}`;
            }

            function renderFiles() {
                const transfer = new DataTransfer();
                selectedFiles.forEach((file) => transfer.items.add(file));
                fileInput.files = transfer.files;
                fileList.replaceChildren();

                selectedFiles.forEach((file, index) => {
                    const row = document.createElement('div');
                    row.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
                    const label = document.createElement('span');
                    label.className = 'text-break';
                    label.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                    remove.textContent = 'Remove';
                    remove.setAttribute('aria-label', `Remove ${file.name}`);
                    remove.addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        renderFiles();
                        updateCountAndPreview();
                    });
                    row.append(label, remove);
                    fileList.append(row);
                });
            }

            function addPhotoFiles(files) {
                let invalidType = false;
                let overLimit = false;
                let duplicate = false;

                Array.from(files).forEach((file) => {
                    if (!/\.(?:jpe?g|png)$/i.test(file.name)) {
                        invalidType = true;
                        return;
                    }
                    if (selectedFiles.some((selected) => fileKey(selected) === fileKey(file))) {
                        duplicate = true;
                        return;
                    }
                    if (totalFileBytes() + file.size > maximumBytes) {
                        overLimit = true;
                        return;
                    }
                    selectedFiles.push(file);
                });

                renderFiles();
                updateCountAndPreview();
                showFeedback(invalidType ? 'Only JPG, JPEG and PNG photos are accepted.'
                    : overLimit ? 'Photo files must total no more than 80 MB.'
                        : duplicate ? 'Duplicate photo files were skipped.' : '');
            }

            function normalizeDriveUrl(value) {
                try {
                    const url = new URL(value.trim());
                    const driveFile = url.hostname === 'drive.google.com' && /^\/file\/d\/[^/]+(?:\/|$)/.test(url.pathname);
                    const driveId = url.hostname === 'drive.google.com' && ['/open', '/uc'].includes(url.pathname) && url.searchParams.has('id');
                    const docsFile = url.hostname === 'docs.google.com' && /^\/(?:document|spreadsheets|presentation|forms)\/d\/[^/]+(?:\/|$)/.test(url.pathname);
                    if (url.protocol !== 'https:' || !(driveFile || driveId || docsFile)) return null;
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
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'drive_links[]';
                    hidden.value = url;
                    driveFields.append(hidden);
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                    remove.textContent = 'Remove';
                    remove.setAttribute('aria-label', 'Remove photo Drive link');
                    remove.addEventListener('click', () => {
                        selectedDriveLinks.splice(index, 1);
                        renderDriveLinks();
                        updateCountAndPreview();
                    });
                    row.append(link, remove);
                    driveList.append(row);
                });
            }

            window.addDriveLink = function addDriveLink() {
                const value = driveInput.value.trim();
                const url = normalizeDriveUrl(value);
                if (!value) {
                    showFeedback('Enter a Google Drive photo share link.');
                    return;
                }
                if (!url) {
                    showFeedback('Enter a valid HTTPS Google Drive photo share link.');
                    return;
                }
                if (selectedDriveLinks.includes(url)) {
                    showFeedback('That Drive link has already been added.');
                    return;
                }
                selectedDriveLinks.push(url);
                driveInput.value = '';
                renderDriveLinks();
                updateCountAndPreview();
                showFeedback('');
            };

            window.calculateTotal = updateCountAndPreview;
            fileInput.addEventListener('change', (event) => addPhotoFiles(event.target.files));
            form.querySelectorAll('input[name="photo_option_id"]').forEach((radio) => radio.addEventListener('change', updateCountAndPreview));
            const uploadBox = document.querySelector('.upload-box[for="photoUpload"]');
            uploadBox.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
            });
            uploadBox.addEventListener('drop', (event) => {
                event.preventDefault();
                addPhotoFiles(event.dataTransfer.files);
            });
            driveInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    window.addDriveLink();
                }
            });
            form.addEventListener('submit', (event) => {
                if (selectedFiles.length + selectedDriveLinks.length === 0) {
                    event.preventDefault();
                    showFeedback('Add at least one photo file or Google Drive link.');
                }
            });
            updateCountAndPreview();
        })();
    </script>
@endpush
