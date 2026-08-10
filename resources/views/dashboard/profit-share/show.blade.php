@extends('layouts.dashboard')

@section('page-title', 'Profit Share Details')

@section('content')
<div class="fade-in">
    <div class="mb-4">
        <a href="{{ route('dashboard.profit-share.index') }}" style="color: var(--text-dim); font-size: 13px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Profit Share
        </a>
    </div>

    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 20px; font-size: 22px;">
        <i class="fas fa-hand-holding-usd" style="color: #10b981;"></i> Profit Share Details
    </h2>

    <div class="row g-4">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card-custom" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-size: 12px; color: var(--text-dim);">Cycle</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--text-bright);">{{ $distribution->cycle_id }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: var(--text-dim);">Amount Received</div>
                        <div style="font-size: 24px; font-weight: 700; color: #10b981;">${{ number_format($distribution->amount, 2) }}</div>
                    </div>
                </div>

                <div style="display: grid; gap: 12px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Pool Amount</span>
                        <span style="color: var(--text);">${{ number_format($distribution->pool_amount, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Your Share %</span>
                        <span style="color: var(--text);">{{ number_format($distribution->share_percentage, 4) }}%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Weighted Capital</span>
                        <span style="color: var(--text);">${{ number_format($distribution->weighted_capital, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Total Weighted Capital</span>
                        <span style="color: var(--text);">${{ number_format($distribution->total_weighted_capital, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Status</span>
                        <span style="color: #10b981; font-weight: 600; text-transform: capitalize;">{{ $distribution->status }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Date</span>
                        <span style="color: var(--text);">{{ \Carbon\Carbon::parse($distribution->created_at)->format('M d, Y H:i') }}</span>
                    </div>
                    @if($distribution->note)
                    <div style="padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim); display: block; margin-bottom: 4px;">Note</span>
                        <span style="color: var(--text);">{{ $distribution->note }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom" style="padding: 24px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
                    <i class="fas fa-box" style="color: #818cf8;"></i> Investment Info
                </h5>
                @if($investment)
                <div style="display: grid; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Package</span>
                        <span style="color: var(--text);">{{ $package?->name ?? 'Unknown' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Category</span>
                        <span style="color: var(--text);">{{ $package?->category ?? '—' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Investment Amount</span>
                        <span style="color: var(--text);">${{ number_format($investment->amount, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Start Date</span>
                        <span style="color: var(--text);">{{ \Carbon\Carbon::parse($investment->start_date ?? $investment->created_at)->format('M d, Y') }}</span>
                    </div>
                    @if($package && $package->profit_share_weight)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Profit Share Weight</span>
                        <span style="color: var(--text);">{{ $package->profit_share_weight }}x</span>
                    </div>
                    @endif
                </div>
                @else
                <p style="color: var(--text-dim); font-size: 13px;">Investment data not available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
