@extends('layouts.dashboard')

@section('page-title', 'Ticket: ' . $ticket->ticket_number)

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('dashboard.support.index') }}" style="color: var(--text-dim); font-size: 13px; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> All Tickets
                </a>
            </div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 20px;">
                <code style="color: #818cf8;">{{ $ticket->ticket_number }}</code> — {{ $ticket->subject }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            @if($ticket->status !== 'closed')
            <form method="POST" action="{{ route('dashboard.support.close', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: rgba(100,116,139,0.15); border: 1px solid rgba(100,116,139,0.3); color: #94a3b8; border-radius: 10px; padding: 8px 16px; font-size: 12px;" onclick="return confirm('Close this ticket?')">
                    <i class="fas fa-check"></i> Close Ticket
                </button>
            </form>
            @endif
        </div>
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

    <!-- Ticket Info Bar -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <div class="row g-3" style="font-size: 13px;">
            <div class="col-md-3">
                <span style="color: var(--text-dim);">Category:</span>
                <span style="color: #818cf8; text-transform: capitalize; font-weight: 600;">{{ $ticket->category }}</span>
            </div>
            <div class="col-md-3">
                <span style="color: var(--text-dim);">Priority:</span>
                @php $pColors = ['low' => '#64748b', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444']; @endphp
                <span style="color: {{ $pColors[$ticket->priority] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
            </div>
            <div class="col-md-3">
                <span style="color: var(--text-dim);">Status:</span>
                @php $sColors = ['open' => '#3b82f6', 'answered' => '#10b981', 'pending' => '#f59e0b', 'closed' => '#64748b']; @endphp
                <span style="color: {{ $sColors[$ticket->status] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">
                    @if($ticket->status === 'answered') Awaiting Reply @else {{ $ticket->status }} @endif
                </span>
            </div>
            <div class="col-md-3">
                <span style="color: var(--text-dim);">Created:</span>
                <span style="color: var(--text-muted);">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Message Thread -->
    <div style="max-width:100%;max-width:800px;">
        @foreach($messages as $msg)
        @php $isMe = $msg->user_id === auth()->id(); @endphp
        <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-direction: {{ $isMe ? 'row-reverse' : 'row' }};">
            <!-- Avatar -->
            <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 14px; background: {{ $msg->is_admin ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #6366f1, #7c3aed)' }};">
                {{ strtoupper(substr($msg->user?->name ?? 'S', 0, 1)) }}
            </div>
            <!-- Message Bubble -->
            <div style="flex: 1; max-width: 75%;">
                <div style="margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <span style="color: var(--text-bright); font-weight: 600; font-size: 13px;">{{ $msg->user?->name ?? 'Unknown' }}</span>
                    @if($msg->is_admin)
                    <span style="font-size: 10px; padding: 1px 6px; border-radius: 4px; background: rgba(239,68,68,0.15); color: #ef4444; font-weight: 600;">SUPPORT TEAM</span>
                    @endif
                    <span style="color: var(--text-dim); font-size: 11px;">{{ $msg->created_at->diffForHumans() }}</span>
                </div>
                <div style="background: {{ $msg->is_admin ? 'rgba(239,68,68,0.08)' : 'rgba(99,102,241,0.08)' }}; border: 1px solid {{ $msg->is_admin ? 'rgba(239,68,68,0.15)' : 'rgba(99,102,241,0.15)' }}; border-radius: 14px; padding: 16px 20px;">
                    <p style="color: var(--text); font-size: 14px; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $msg->message }}</p>
                    
                    <!-- Attachments -->
                    @if($msg->attachments)
                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid rgba(99,102,241,0.1); display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($msg->attachments as $att)
                        <a href="{{ asset('storage/' . $att['path']) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.15); color: #818cf8; font-size: 12px; text-decoration: none;">
                            @php
                                $icon = 'paperclip';
                                $ext = pathinfo($att['name'], PATHINFO_EXTENSION);
                                if (in_array($ext, ['jpg','jpeg','png','gif'])) $icon = 'image';
                                elseif ($ext === 'pdf') $icon = 'file-pdf';
                                elseif (in_array($ext, ['doc','docx'])) $icon = 'file-word';
                                elseif ($ext === 'zip') $icon = 'file-archive';
                                else $icon = 'paperclip';
                            @endphp
                            <i class="fas fa-{{ $icon }}"></i> {{ \Illuminate\Support\Str::limit($att['name'], 25) }}
                            <span style="color: var(--text-dim); font-size: 10px;">({{ round($att['size'] / 1024) }}KB)</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <!-- Reply Form (only if ticket is not closed) -->
        @if($ticket->status !== 'closed')
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <form method="POST" action="{{ route('dashboard.support.reply', $ticket) }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex gap-2">
                    <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 14px; background: linear-gradient(135deg, #6366f1, #7c3aed);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="flex: 1;">
                        <textarea name="message" rows="3" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 14px; padding: 14px 18px; font-size: 14px; resize: vertical;" placeholder="Type your reply..." required></textarea>
                        
                        <!-- Attachment input -->
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                            <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.15); color: #818cf8; font-size: 12px;">
                                <i class="fas fa-paperclip"></i> Attach files
                                <input type="file" name="attachments[]" multiple style="display:none;" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip" onchange="updateFileNames(this)">
                            </label>
                            <span id="fileNames" style="font-size: 11px; color: var(--text-dim);"></span>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-2">
                            <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 10px 24px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @else
        <!-- Ticket closed — show rating form if not rated, or show rating -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            @if($ticket->rating)
            <!-- Already rated -->
            <div style="text-align: center; padding: 24px; background: rgba(99,102,241,0.05); border-radius: 14px; border: 1px solid rgba(99,102,241,0.1);">
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Your rating for this ticket:</div>
                <div style="font-size: 24px; letter-spacing: 4px;">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $ticket->rating) ⭐ @else ☆ @endif
                    @endfor
                </div>
                @if($ticket->rating_comment)
                <div style="font-size: 13px; color: var(--text); margin-top: 8px; font-style: italic;">"{{ $ticket->rating_comment }}"</div>
                @endif
            </div>
            @else
            <!-- Rating form -->
            <div style="background: rgba(99,102,241,0.05); border-radius: 14px; padding: 24px; border: 1px solid rgba(99,102,241,0.1);">
                <div style="text-align: center; margin-bottom: 16px;">
                    <i class="fas fa-star" style="font-size: 28px; color: #f59e0b; opacity: 0.5;"></i>
                    <div style="font-size: 15px; font-weight: 600; color: var(--text-bright); margin-top: 8px;">Rate your support experience</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Your feedback helps us improve our support quality.</div>
                </div>
                <form method="POST" action="{{ route('dashboard.support.rate', $ticket) }}">
                    @csrf
                    <div style="text-align: center; margin-bottom: 16px;">
                        <div style="font-size: 32px; letter-spacing: 6px; cursor: pointer;" id="starRating">
                            <span data-val="1" onclick="setRating(1)">⭐</span>
                            <span data-val="2" onclick="setRating(2)">⭐</span>
                            <span data-val="3" onclick="setRating(3)">⭐</span>
                            <span data-val="4" onclick="setRating(4)">⭐</span>
                            <span data-val="5" onclick="setRating(5)">⭐</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="5" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="rating_comment" rows="2" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px 14px; font-size: 13px; resize: vertical;" placeholder="Tell us about your experience (optional)"></textarea>
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 10px 28px; font-size: 13px; font-weight: 600;">
                            <i class="fas fa-star"></i> Submit Rating
                        </button>
                    </div>
                </form>
            </div>
            @endif
            <div style="text-align: center; padding: 16px; color: var(--text-dim); font-size: 14px;">
                <i class="fas fa-lock" style="font-size: 24px; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                This ticket has been closed. <a href="{{ route('dashboard.support.create') }}" style="color: #818cf8; text-decoration: none;">Create a new ticket</a> if you need further help.
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    const stars = document.querySelectorAll('#starRating span');
    stars.forEach((s, i) => {
        s.textContent = i < val ? '⭐' : '☆';
    });
}

function updateFileNames(input) {
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        document.getElementById('fileNames').textContent = names;
    }
}
</script>
@endsection