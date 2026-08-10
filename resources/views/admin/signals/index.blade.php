@extends('layouts.admin')

@section('page-title', 'Trading Signals')

@section('content')
<div class="fade-in">
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-broadcast-tower"></i></div>
                <div class="stat-label">Total Signals</div>
                <div class="stat-value">{{ $totalSignals }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-signal"></i></div>
                <div class="stat-label">Active Now</div>
                <div class="stat-value">{{ $activeCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Wins</div>
                <div class="stat-value">{{ $winCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-label">Losses</div>
                <div class="stat-value">{{ $lossCount }}</div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight: 700;"><i class="fas fa-broadcast-tower" style="color: var(--purple-3);"></i> Trading Signals</h4>
        <a href="{{ route('admin.signals.create') }}" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600; text-decoration: none;">
            <i class="fas fa-plus"></i> New Signal
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Signals Table -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Direction</th>
                        <th>Category</th>
                        <th>Entry</th>
                        <th>SL</th>
                        <th>TP</th>
                        <th>Confidence</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($signals as $signal)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-bright);">{{ $signal->symbol }}</td>
                        <td>
                            <span class="badge-custom {{ $signal->direction === 'buy' ? 'badge-up' : 'badge-down' }}" style="font-size: 12px;">
                                {{ strtoupper($signal->direction) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-custom badge-purple" style="font-size: 11px;">{{ strtoupper($signal->category) }}</span>
                        </td>
                        <td>${{ number_format($signal->entry_price, $signal->entry_price < 1 ? 4 : 2) }}</td>
                        <td style="color: #ef4444;">${{ number_format($signal->stop_loss, $signal->stop_loss < 1 ? 4 : 2) }}</td>
                        <td style="color: #10b981;">${{ number_format($signal->take_profit, $signal->take_profit < 1 ? 4 : 2) }}</td>
                        <td>
                            @if($signal->confidence > 0)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 50px; height: 5px; background: var(--bg-input); border-radius: 3px; overflow: hidden;">
                                    <div style="width: {{ $signal->confidence }}%; height: 100%; background: var(--gradient-primary);"></div>
                                </div>
                                <span style="font-size: 11px;">{{ $signal->confidence }}%</span>
                            </div>
                            @else
                            <span style="color: var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($signal->status === 'active')
                            <span class="badge-custom badge-up"><i class="fas fa-circle" style="font-size: 6px;"></i> Active</span>
                            @else
                            <span class="badge-custom" style="background: rgba(100,116,139,0.2); color: #94a3b8;">Closed</span>
                            @endif
                        </td>
                        <td>
                            @if($signal->result === 'win')
                            <span class="badge-custom badge-up">Win</span>
                            @elseif($signal->result === 'loss')
                            <span class="badge-custom badge-down">Loss</span>
                            @elseif($signal->result === 'breakeven')
                            <span class="badge-custom" style="background: rgba(245,158,11,0.2); color: #f59e0b;">BE</span>
                            @else
                            <span style="color: var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($signal->status === 'active')
                            <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#closeSignal{{ $signal->id }}" style="background: rgba(99,102,241,0.15); color: var(--purple-3); border: 1px solid rgba(99,102,241,0.3); font-size: 12px;">
                                <i class="fas fa-times"></i> Close
                            </button>
                            @endif
                            <form action="{{ route('admin.signals.destroy', $signal) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this signal?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-size: 12px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Close Modal -->
                    <div class="modal fade" id="closeSignal{{ $signal->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border);">
                                <div class="modal-header">
                                    <h5 class="modal-title">Close {{ $signal->symbol }} Signal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('admin.signals.close', $signal) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label">Result</label>
                                            <select name="result" class="form-control" required>
                                                <option value="win">Win</option>
                                                <option value="loss">Loss</option>
                                                <option value="breakeven">Breakeven</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Close Price (optional)</label>
                                            <input type="number" name="close_price" class="form-control" step="0.0001" />
                                        </div>
                                        <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white;">Close Signal</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $signals->links() }}
    </div>
</div>
@endsection
