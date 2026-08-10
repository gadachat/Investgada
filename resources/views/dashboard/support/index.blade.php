@extends('layouts.dashboard')

@section('page-title', 'Support Tickets')

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-headset" style="color: #6366f1;"></i> Support Tickets
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Get help with deposits, withdrawals, account issues, and more.</p>
        </div>
        <a href="{{ route('dashboard.support.create') }}" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 12px; padding: 10px 24px; font-size: 13px; text-decoration: none; font-weight: 600;">
            <i class="fas fa-plus"></i> New Ticket
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-inbox"></i></div>
                <div class="stat-label">Open Tickets</div>
                <div class="stat-value">{{ $stats['open'] }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check"></i></div>
                <div class="stat-label">Closed</div>
                <div class="stat-value">{{ $stats['closed'] }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>

    <!-- Ticket List -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 14px 20px;">Ticket #</th>
                        <th style="padding: 14px 20px;">Subject</th>
                        <th style="padding: 14px 20px;">Category</th>
                        <th style="padding: 14px 20px;">Priority</th>
                        <th style="padding: 14px 20px;">Status</th>
                        <th style="padding: 14px 20px;">Last Updated</th>
                        <th style="padding: 14px 20px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2); cursor: pointer;" onclick="window.location='{{ route('dashboard.support.show', $ticket) }}'">
                        <td style="padding: 14px 20px;"><code style="color: #818cf8; font-weight: 600;">{{ $ticket->ticket_number }}</code></td>
                        <td style="padding: 14px 20px; color: var(--text-bright);">{{ $ticket->subject }}</td>
                        <td style="padding: 14px 20px;">
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(99,102,241,0.1); color: #818cf8; text-transform: capitalize;">{{ $ticket->category }}</span>
                        </td>
                        <td style="padding: 14px 20px;">
                            @php
                                $priorityColors = ['low' => '#64748b', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444'];
                                $pColor = $priorityColors[$ticket->priority] ?? '#64748b';
                            @endphp
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: {{ $pColor }}20; color: {{ $pColor }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
                        </td>
                        <td style="padding: 14px 20px;">
                            @php
                                $statusColors = ['open' => '#3b82f6', 'answered' => '#10b981', 'pending' => '#f59e0b', 'closed' => '#64748b'];
                                $sColor = $statusColors[$ticket->status] ?? '#64748b';
                            @endphp
                            <span style="font-size: 11px; padding: 3px 10px; border-radius: 20px; background: {{ $sColor }}20; color: {{ $sColor }}; text-transform: capitalize; font-weight: 600;">
                                @if($ticket->status === 'answered') Awaiting Reply @else {{ $ticket->status }} @endif
                            </span>
                        </td>
                        <td style="padding: 14px 20px; color: var(--text-dim); font-size: 12px;">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td style="padding: 14px 20px;"><i class="fas fa-chevron-right" style="color: var(--text-dim);"></i></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px; color: var(--text-dim);">
                            <i class="fas fa-ticket-alt" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                            <p style="font-size: 14px; margin: 0 0 16px;">No support tickets yet.</p>
                            <a href="{{ route('dashboard.support.create') }}" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 12px; padding: 10px 24px; font-size: 13px; text-decoration: none; font-weight: 600;">
                                <i class="fas fa-plus"></i> Create Your First Ticket
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $tickets->links() }}
</div>
@endsection