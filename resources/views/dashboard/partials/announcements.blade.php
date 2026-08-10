@php
    $announcements = \App\Models\Announcement::active()->forUser(auth()->user())->get();
    $dismissed = session('dismissed_announcements', []);
@endphp

@if($announcements->count() > 0)
<div class="announcements-container" style="margin-bottom:20px;">
    @foreach($announcements as $announcement)
    @if(!in_array($announcement->id, $dismissed))
    @php
        $colors = [
            'info'        => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'icon' => 'fa-info-circle'],
            'success'     => ['bg' => '#d1fae5', 'text' => '#059669', 'icon' => 'fa-check-circle'],
            'warning'     => ['bg' => '#fef3c7', 'text' => '#d97706', 'icon' => 'fa-exclamation-triangle'],
            'danger'      => ['bg' => '#fee2e2', 'text' => '#dc2626', 'icon' => 'fa-times-circle'],
            'maintenance'=> ['bg' => '#f3e8ff', 'text' => '#7c3aed', 'icon' => 'fa-tools'],
        ];
        $c = $colors[$announcement->type] ?? $colors['info'];
    @endphp
    <div class="card-custom" style="background:{{ $c['bg'] }}; border:none; border-left:4px solid {{ $c['text'] }}; margin-bottom:10px; padding:16px 20px;">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div style="font-weight:600; color:{{ $c['text'] }}; font-size:14px; margin-bottom:4px;">
                    <i class="fas {{ $c['icon'] }} me-2"></i>{{ $announcement->title }}
                </div>
                <div style="color:var(--text-dim); font-size:13px; line-height:1.5;">{{ $announcement->message }}</div>
            </div>
            @if($announcement->is_dismissible)
            <form method="POST" action="{{ route('dashboard.announcements.dismiss') }}" style="margin-left:12px;">
                @csrf
                <input type="hidden" name="announcement_id" value="{{ $announcement->id }}">
                <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:16px;">&times;</button>
            </form>
            @endif
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif
