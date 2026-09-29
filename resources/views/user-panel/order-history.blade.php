@extends('user-panel.layout.app')

@section('content')


                    <div class="order-history-card">

                        <!-- Header -->
                        <div class="d-flex flex-column flex-md-row
                justify-content-between align-items-md-center
                gap-3 mb-4">

                            <div>
                                <h3 class="fw-bold mb-1">
                                    <i class="bi bi-clock-history text-primary me-2"></i>
                                    Order History
                                </h3>

                                <p class="text-muted mb-0">
                                    View and manage your previous orders
                                </p>
                            </div>

                        </div>


                        <!-- Filter -->
                        <div class="filter-box mb-4">

                            <div class="row g-3 align-items-end">

                                <!-- From -->
                                <div class="col-12 col-md-4">

                                    <label class="form-label fw-semibold">
                                        From
                                    </label>

                                    <input type="date"
                                        class="form-control"
                                        id="fromDate">
                                </div>


                                <!-- To -->
                                <div class="col-12 col-md-4">

                                    <label class="form-label fw-semibold">
                                        To
                                    </label>

                                    <input type="date"
                                        class="form-control"
                                        id="toDate">
                                </div>


                                <!-- Button -->
                                <div class="col-12 col-md-4">

                                    <button type="button"
                                        class="btn btn-primary w-100"
                                        onclick="viewOrderData()">

                                        <i class="bi bi-search me-2"></i>
                                        View Data

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- Order Table -->
                        <div class="table-responsive">

                            <table class="table order-table align-middle">

                                <thead>
                                    <tr>
                                        <th>DATE &amp; ID</th>
                                        <th>ORDER DETAILS &amp; PDFS</th>
                                        <th>PAID</th>
                                        <th>LIVE STATUS</th>
                                    </tr>
                                </thead>

                                <tbody id="orderHistoryBody">

                                    <!-- Empty State -->

                                    <tr id="emptyOrderRow">

                                        <td colspan="4" class="text-center py-5">

                                            <div class="empty-order">

                                                <i class="bi bi-calendar2-x"></i>

                                                <h6 class="fw-bold mt-3">No Orders Found
                                                </h6>

                                                <p class="text-muted mb-0">
                                                    Please select a start and end date,
                                then click <strong>View Data</strong>.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                
@endsection
