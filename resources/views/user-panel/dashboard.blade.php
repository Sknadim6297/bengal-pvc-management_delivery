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
                            @forelse ($liveOrderFeed as $liveOrder)
                                <div class="order-item">
                                    <div class="order-avatar">{{ mb_strtoupper(mb_substr($liveOrder->user?->name ?? 'U', 0, 1)) }}</div>
                                    <div class="order-info">
                                        <strong>{{ $liveOrder->user?->name ?? 'Customer' }} ordered {{ $liveOrder->quantity }} {{ $liveOrder->quantity === 1 ? 'card' : 'cards' }}</strong>
                                        <div class="order-meta">
                                            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                                                                                                                          {{ data_get($liveOrder->delivery_address, 'district', $liveOrder->user?->district ?? '—') }}
                                            &nbsp; • &nbsp;
                                            {{ $liveOrder->created_at?->format('h:i a') }}
                                        </div>
                                    </div>
                                    <span class="order-status">{{ ucfirst(str_replace('_', ' ', $liveOrder->status)) }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4" role="status">No live orders yet</div>
                            @endforelse
                        </div>

                        <div class="feed-header mt-4">
                            <div class="feed-title"><i class="bi bi-clock-history" aria-hidden="true"></i> My Recent Orders</div>
                            <a href="{{ route('user.order-history') }}" class="small">Order history</a>
                        </div>
                        <div class="feed-box">
                            @forelse ($recentOrders as $recentOrder)
                                <a href="{{ route('user.orders.show', $recentOrder->id) }}" class="order-item text-decoration-none">
                                    <div class="order-avatar"><i class="bi {{ $recentOrder->service_type === 'photo-print' ? 'bi-image-fill' : 'bi-credit-card-2-front-fill' }}" aria-hidden="true"></i></div>
                                    <div class="order-info">
                                        <strong>{{ $recentOrder->order_number }} · {{ $recentOrder->quantity }} {{ $recentOrder->service_type === 'photo-print' ? ($recentOrder->quantity === 1 ? 'photo' : 'photos') : ($recentOrder->quantity === 1 ? 'card' : 'cards') }}</strong>
                                        <div class="order-meta"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $recentOrder->created_at?->format('Y-m-d H:i') }}</div>
                                    </div>
                                    <span class="order-status">{{ ucfirst(str_replace('_', ' ', $recentOrder->status)) }}</span>
                                </a>
                            @empty
                                <div class="text-center text-muted py-4" role="status">No recent orders yet</div>
                            @endforelse
                        </div>

                    </div>

                
@endsection
