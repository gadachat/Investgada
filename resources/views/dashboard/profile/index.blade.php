@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Profile Info -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="user-avatar me-3" style="width: 64px; height: 64px; font-size: 24px; border-radius: 16px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $user->name }}</h4>
                            <p class="text-muted mb-0">{{ $user->email }}</p>
                            <span class="badge bg-primary mt-1">{{ ucfirst($user->role ?? 'User') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('dashboard.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $user->country ?? '') }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address ?? '') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Account Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 40%">Account ID</td>
                            <td><code>#{{ $user->id }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since</td>
                            <td>{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Status</td>
                            <td>
                                @if($user->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                        </tr>
                        @if($user->kyc_status ?? null)
                        <tr>
                            <td class="text-muted">KYC Status</td>
                            <td>
                                @php $kycClass = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$user->kyc_status] ?? 'secondary'; @endphp
                                <span class="badge bg-{{ $kycClass }}">{{ ucfirst($user->kyc_status) }}</span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
