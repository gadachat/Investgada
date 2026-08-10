@extends('layouts.admin')

@section('page-title', $package ? 'Edit Package' : 'New Package')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 24px; font-size: 22px;"><i class="fas fa-chart-pie" style="color: var(--purple-3);"></i> {{ $package ? 'Edit Package' : 'Create Package' }}</h2>

    <div class="card-custom" style="max-width:100%;max-width:800px;">
        <form method="POST" action="{{ $package ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
            @csrf
            @if($package) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-8"><label style="font-size: 13px; color: var(--text-muted);">Package Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $package?->name) }}" required></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $package?->sort_order ?? 0) }}"></div>
                <div class="col-12"><label style="font-size: 13px; color: var(--text-muted);">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description', $package?->description) }}</textarea></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Category</label><select name="category" class="form-control">{{ ['crypto','forex','stocks','bonds','binary','mixed'] | array_map(fn($c) => '<option value="' . $c . '" ' . (old('category', $package?->category) === $c ? 'selected' : '') . '>' . ucfirst($c) . '</option>') | join('') }}</select></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Type</label><select name="type" class="form-control"><option value="fixed" @selected(old('type', $package?->type) === 'fixed')>Fixed</option><option value="variable" @selected(old('type', $package?->type) === 'variable')>Variable</option><option value="profit_share" @selected(old('type', $package?->type) === 'profit_share')>Profit Share</option></select></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Return Type</label><select name="return_type" class="form-control"><option value="daily" @selected(old('return_type', $package?->return_type) === 'daily')>Daily</option><option value="weekly" @selected(old('return_type', $package?->return_type) === 'weekly')>Weekly</option><option value="monthly" @selected(old('return_type', $package?->return_type) === 'monthly')>Monthly</option><option value="maturity" @selected(old('return_type', $package?->return_type) === 'maturity')>At Maturity</option></select></div>
                <div class="col-md-3"><label style="font-size: 13px; color: var(--text-muted);">Return Rate (%)</label><input type="number" name="return_rate" class="form-control" value="{{ old('return_rate', $package?->return_rate ?? 5) }}" step="0.01" required></div>
                <div class="col-md-3"><label style="font-size: 13px; color: var(--text-muted);">Min Amount ($)</label><input type="number" name="min_amount" class="form-control" value="{{ old('min_amount', $package?->min_amount ?? 100) }}" step="0.01" required></div>
                <div class="col-md-3"><label style="font-size: 13px; color: var(--text-muted);">Max Amount ($)</label><input type="number" name="max_amount" class="form-control" value="{{ old('max_amount', $package?->max_amount) }}" step="0.01"></div>
                <div class="col-md-3"><label style="font-size: 13px; color: var(--text-muted);">Return Cap (%)</label><input type="number" name="total_return_cap" class="form-control" value="{{ old('total_return_cap', $package?->total_return_cap ?? 0) }}" step="0.01"></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Duration (days)</label><input type="number" name="duration_days" class="form-control" value="{{ old('duration_days', $package?->duration_days ?? 30) }}" required></div>
                <div class="col-md-4"><label style="font-size: 13px; color: var(--text-muted);">Cycle (days)</label><input type="number" name="cycle_days" class="form-control" value="{{ old('cycle_days', $package?->cycle_days ?? 1) }}" required></div>
                <div class="col-md-4" style="display: flex; align-items: flex-end; gap: 16px; padding-bottom: 14px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted);"><input type="checkbox" name="principal_return" {{ old('principal_return', $package?->principal_return ?? true) ? 'checked' : '' }}> Principal Return</label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted);"><input type="checkbox" name="compounding" {{ old('compounding', $package?->compounding ?? false) ? 'checked' : '' }}> Compounding</label>
                </div>
                <div class="col-md-6" style="display: flex; align-items: flex-end; gap: 16px; padding-bottom: 14px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted);"><input type="checkbox" name="is_active" {{ old('is_active', $package?->is_active ?? true) ? 'checked' : '' }}> Active</label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted);"><input type="checkbox" name="featured" {{ old('featured', $package?->featured ?? false) ? 'checked' : '' }}> Featured</label>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-gradient"><i class="fas fa-save"></i> {{ $package ? 'Update Package' : 'Create Package' }}</button>
                <a href="{{ route('admin.packages.index') }}" class="btn-outline-custom" style="text-decoration: none;"><i class="fas fa-arrow-left"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
