@extends('user-panel.layout.app')

@section('content')


                    <div class="content-card">

                        <!-- Welcome -->

                        <h1 class="welcome-title">Welcome, <span>{{ $user->name ?: 'User' }}!</span>
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
                                            {{ number_format($statistics->processing_cards) }}
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
                                            {{ number_format($statistics->delivered_cards) }}
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

                            <a href="{{ route('user.pvc-card-print') }}" class="action-btn text-decoration-none d-flex align-items-center">

                                <i class="bi bi-plus-circle-fill"></i>

                                Start a New Order

                            </a>


                            @if (filled(config('services.whatsapp_channel_url')))
                                <a class="action-btn text-decoration-none d-flex align-items-center" href="{{ config('services.whatsapp_channel_url') }}" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-megaphone-fill"></i>
                                    Join Whatsapp Channel
                                </a>
                            @else
                                <button class="action-btn" type="button" disabled aria-disabled="true" title="WhatsApp channel link is not configured">
                                    <i class="bi bi-megaphone-fill"></i>
                                    Join Whatsapp Channel
                                </button>
                            @endif

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
                            <div class="text-center text-muted py-4" role="status">No live orders yet</div>
                        </div>

                    </div>

                
@endsection
