@extends('layouts.admin')

@section('page-title', 'Deposit Addresses')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;"><i class="fas fa-qrcode" style="color: var(--purple-3);"></i> Crypto Deposit Addresses</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Manage wallet addresses shown to users for crypto deposits.</p>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card-custom mb-3">
        <h5 style="color: var(--text-bright); margin-bottom: 16px;"><i class="fas fa-plus-circle" style="color: var(--green);"></i> Add Address</h5>
        <form method="POST" action="{{ route('admin.settings.addresses.store') }}" class="row g-3">
            @csrf
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Network</label><select name="network" class="form-control" required><option>TRC20</option><option>ERC20</option><option>BEP20</option><option>BTC</option><option>SOL</option></select></div>
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Coin</label><select name="coin" class="form-control" required><option>USDT</option><option>BTC</option><option>ETH</option><option>BNB</option><option>SOL</option></select></div>
            <div class="col-md-5"><label style="font-size: 12px; color: var(--text-muted);">Wallet Address</label><input type="text" name="address" class="form-control" placeholder="0x... or T..." required></div>
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">QR Code URL (optional)</label><input type="text" name="qr_code" class="form-control" placeholder="https://..."></div>
            <div class="col-12"><button type="submit" class="btn-gradient"><i class="fas fa-plus"></i> Add Address</button></div>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Network</th><th>Coin</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @foreach($addresses as $addr)
                <tr>
                    <td><span class="badge-custom badge-purple">{{ $addr->network }}</span></td>
                    <td><span class="badge-custom badge-info">{{ $addr->coin }}</span></td>
                    <td style="font-family: monospace; font-size: 12px; color: var(--text-bright);">{{ $addr->address }}</td>
                    <td>
                        @if($addr->is_active)<span class="badge-custom badge-up">Active</span>
                        @else<span class="badge-custom badge-down">Inactive</span>@endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <form method="POST" action="{{ route('admin.settings.addresses.toggle', $addr) }}" style="display: inline;">@csrf @method('PATCH')
                                <button type="submit" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px;"><i class="fas fa-toggle-on"></i> Toggle</button>
                            </form>
                            <form method="POST" action="{{ route('admin.settings.addresses.destroy', $addr) }}" style="display: inline;">@csrf @method('DELETE')
                                <button type="submit" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: rgba(239,68,68,0.3);" onclick="return confirm('Delete this address?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($addresses->isEmpty())
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-dim);">No deposit addresses added yet.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
