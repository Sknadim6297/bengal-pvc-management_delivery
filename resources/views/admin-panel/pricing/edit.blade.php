@extends('admin-panel.layout.app')

@section('title', 'PVC Pricing Settings | India PVC')

@section('content')
    <div class="content-card">
        <div class="mb-4">
            <h1 class="welcome-title mb-1">PVC Pricing Settings</h1>
            <p class="text-muted mb-0">Rates shown on the PVC Card Print page</p>
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

        <form method="POST" action="{{ route('admin.pricing.update') }}">
            @csrf
            @method('PUT')

            <h2 class="h5 fw-bold mb-3">Normal Quality</h2>
            <div class="row g-3 mb-4">
                @foreach ([
                    'normal_single_price' => '1 card rate (₹)',
                    'normal_2_5_price' => '2–5 cards rate (₹)',
                    'normal_2_5_discount' => '2–5 cards discount (%)',
                    'normal_6_7_price' => '6–7 cards rate (₹)',
                    'normal_6_7_discount' => '6–7 cards discount (%)',
                    'normal_8_10_price' => '8–10 cards rate (₹)',
                    'normal_8_10_discount' => '8–10 cards discount (%)',
                    'normal_11_plus_price' => '11+ cards rate (₹)',
                    'normal_11_plus_discount' => '11+ cards discount (%)',
                ] as $field => $label)
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" step="1" class="form-control" value="{{ old($field, $pricing->{$field}) }}" required>
                    </div>
                @endforeach
            </div>

            <h2 class="h5 fw-bold mb-3">Premium Quality</h2>
            <div class="row g-3 mb-4">
                @foreach ([
                    'premium_under_12_price' => '1–11 cards rate (₹)',
                    'premium_12_plus_price' => '12+ cards rate (₹)',
                ] as $field => $label)
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" step="1" class="form-control" value="{{ old($field, $pricing->{$field}) }}" required>
                    </div>
                @endforeach
            </div>

            <h2 class="h5 fw-bold mb-3">Delivery and Add-ons</h2>
            <div class="row g-3 mb-4">
                @foreach ([
                    'shipping_fee' => 'Flat shipping fee (₹)',
                    'free_shipping_minimum_quantity' => 'Free shipping from quantity',
                    'card_cover_price' => 'Printed card cover per card (₹)',
                ] as $field => $label)
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" min="{{ $field === 'free_shipping_minimum_quantity' ? 1 : 0 }}" step="1" class="form-control" value="{{ old($field, $pricing->{$field}) }}" required>
                    </div>
                @endforeach
            </div>

            <h2 class="h5 fw-bold mb-2">Photo Print Pricing</h2>
            <p class="text-muted">Active options appear on the existing Photo Print page. Existing orders keep their saved price.</p>
            <div id="photoOptions" class="d-flex flex-column gap-3 mb-3">
                @foreach ($photoOptions as $index => $option)
                    <div class="border rounded-3 p-3 photo-option-row">
                        <input type="hidden" name="photo_options[{{ $index }}][id]" value="{{ $option['id'] }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="photo-name-{{ $option['id'] }}">Option name</label>
                                <input id="photo-name-{{ $option['id'] }}" name="photo_options[{{ $index }}][name]" class="form-control" value="{{ old("photo_options.$index.name", $option['name']) }}" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="photo-slug-{{ $option['id'] }}">Code</label>
                                <input id="photo-slug-{{ $option['id'] }}" name="photo_options[{{ $index }}][slug]" class="form-control" value="{{ old("photo_options.$index.slug", $option['slug']) }}" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="photo-price-{{ $option['id'] }}">Rate / photo (₹)</label>
                                <input id="photo-price-{{ $option['id'] }}" name="photo_options[{{ $index }}][unit_price]" type="number" min="0" step="0.01" class="form-control" value="{{ old("photo_options.$index.unit_price", $option['unit_price']) }}" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="photo-order-{{ $option['id'] }}">Display order</label>
                                <input id="photo-order-{{ $option['id'] }}" name="photo_options[{{ $index }}][display_order]" type="number" min="0" class="form-control" value="{{ old("photo_options.$index.display_order", $option['display_order']) }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="hidden" name="photo_options[{{ $index }}][enabled]" value="0">
                                <div class="form-check mb-2">
                                    <input id="photo-enabled-{{ $option['id'] }}" name="photo_options[{{ $index }}][enabled]" type="checkbox" value="1" class="form-check-input" @checked(old("photo_options.$index.enabled", $option['enabled']))>
                                    <label for="photo-enabled-{{ $option['id'] }}" class="form-check-label">Enabled</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="photo-description-{{ $option['id'] }}">Description</label>
                                <input id="photo-description-{{ $option['id'] }}" name="photo_options[{{ $index }}][description]" class="form-control" value="{{ old("photo_options.$index.description", $option['description']) }}" maxlength="255">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button id="addPhotoOption" type="button" class="admin-link-button mb-3"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add photo size</button>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-4">
                    <label for="photo_shipping_fee" class="form-label">Photo shipping (₹)</label>
                    <input id="photo_shipping_fee" name="photo_shipping_fee" type="number" min="0" step="0.01" class="form-control" value="{{ old('photo_shipping_fee', $photoShippingFee) }}" required>
                </div>
            </div>

            <button type="submit" class="admin-search-button"><i class="bi bi-floppy" aria-hidden="true"></i> Save pricing</button>
        </form>

        <template id="photoOptionTemplate">
            <div class="border rounded-3 p-3 photo-option-row">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3"><label class="form-label">Option name</label><input name="photo_options[__key__][name]" class="form-control" required></div>
                    <div class="col-6 col-md-2"><label class="form-label">Code</label><input name="photo_options[__key__][slug]" class="form-control" required></div>
                    <div class="col-6 col-md-2"><label class="form-label">Rate / photo (₹)</label><input name="photo_options[__key__][unit_price]" type="number" min="0" step="0.01" class="form-control" required></div>
                    <div class="col-6 col-md-2"><label class="form-label">Display order</label><input name="photo_options[__key__][display_order]" type="number" min="0" class="form-control" value="0"></div>
                    <div class="col-6 col-md-3"><input type="hidden" name="photo_options[__key__][enabled]" value="0"><div class="form-check mb-2"><input name="photo_options[__key__][enabled]" type="checkbox" value="1" class="form-check-input" checked><label class="form-check-label">Enabled</label></div></div>
                    <div class="col-12"><label class="form-label">Description</label><input name="photo_options[__key__][description]" class="form-control" maxlength="255"></div>
                </div>
            </div>
        </template>
    </div>
    <script>
        (() => {
            const container = document.getElementById('photoOptions');
            const template = document.getElementById('photoOptionTemplate');
            let nextPhotoOption = 0;

            document.getElementById('addPhotoOption').addEventListener('click', () => {
                const row = template.content.firstElementChild.cloneNode(true);
                const key = `new_${nextPhotoOption++}`;
                row.querySelectorAll('[name]').forEach((input) => {
                    input.name = input.name.replaceAll('__key__', key);
                });
                container.append(row);
            });
        })();
    </script>
@endsection