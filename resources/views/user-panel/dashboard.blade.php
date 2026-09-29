@extends('user-panel.layout.app')

@section('content')


                    <div class="content-card">

                        <!-- Welcome -->

                        <h1 class="welcome-title">Welcome, <span>raj chak!</span>
                        </h1>


                        <!-- STAT CARDS -->

                        <div class="row g-4">

                            <!-- Processing -->

                            <div class="col-md-6">

                                <div class="stat-card processing-card">

                                    <div>

                                        <div class="stat-title">
                                            PROCESSING CARDS
                                        </div>

                                        <div class="stat-number">
                                            0
                                        </div>

                                    </div>

                                    <div class="stat-icon">
                                        <i class="bi bi-gear-wide-connected"></i>
                                    </div>

                                </div>

                            </div>


                            <!-- Delivered -->

                            <div class="col-md-6">

                                <div class="stat-card delivered-card">

                                    <div>

                                        <div class="stat-title">
                                            DELIVERED CARDS
                                        </div>

                                        <div class="stat-number">
                                            0
                                        </div>

                                    </div>

                                    <div class="stat-icon">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ACTION BUTTONS -->

                        <div class="action-buttons">

                            <button class="action-btn">

                                <i class="bi bi-plus-circle-fill"></i>

                                Start a New Order

                            </button>


                            <button class="action-btn">

                                <i class="bi bi-megaphone-fill"></i>

                                Join Whatsapp Channel

                            </button>

                        </div>


                        <!-- LIVE FEED -->

                        <div class="feed-header">

                            <div class="feed-title">

                                <i class="bi bi-broadcast"></i>

                                Live Order Feed

                            </div>

                            <span class="real-time">REAL-TIME
                            </span>

                        </div>


                        <div class="feed-box">

                            <!-- Order -->

                            <div class="order-item">

                                <div class="order-avatar">
                                    D
                                </div>

                                <div class="order-info">

                                    <strong>Dipen Samanta ordered 11 cards
                                    </strong>

                                    <div class="order-meta">

                                        <i class="bi bi-geo-alt-fill"></i>
                                        Purba Medinipur
                                    &nbsp; • &nbsp;
                                    07:33 pm

                                    </div>

                                </div>

                                <span class="order-status">PRINTING
                                </span>

                            </div>


                            <div class="order-item">

                                <div class="order-avatar">
                                    A
                                </div>

                                <div class="order-info">

                                    <strong>Amit Das ordered 5 cards
                                    </strong>

                                    <div class="order-meta">

                                        <i class="bi bi-geo-alt-fill"></i>
                                        Kolkata
                                    &nbsp; • &nbsp;
                                    07:25 pm

                                    </div>

                                </div>

                                <span class="order-status">PRINTING
                                </span>

                            </div>


                            <div class="order-item">

                                <div class="order-avatar">
                                    S
                                </div>

                                <div class="order-info">

                                    <strong>Sujoy Roy ordered 8 cards
                                    </strong>

                                    <div class="order-meta">

                                        <i class="bi bi-geo-alt-fill"></i>
                                        Howrah
                                    &nbsp; • &nbsp;
                                    07:18 pm

                                    </div>

                                </div>

                                <span class="order-status">PRINTING
                                </span>

                            </div>

                        </div>

                    </div>

                
@endsection
