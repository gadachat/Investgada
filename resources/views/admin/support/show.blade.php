@extends('layouts.admin')

@section('page-title', 'Ticket: ' . $ticket->ticket_number)

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.support.index') }}" style="color: var(--text-dim); font-size: 13px; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Inbox
                </a>
            </div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 20px;">
                <code style="color: #818cf8;">{{ $ticket->ticket_number }}</code> — {{ $ticket->subject }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            <!-- Priority dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 8px 14px; font-size: 12px;" data-bs-toggle="dropdown">
                    <i class="fas fa-flag"></i> Priority: <span style="text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
                </button>
                <div class="dropdown-menu" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;">
                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                    <form method="POST" action="{{ route('admin.support.priority', $ticket) }}">
                        @csrf
                        <input type="hidden" name="priority" value="{{ $p }}">
                        <button type="submit" class="dropdown-item" style="color: var(--text); font-size: 13px; padding: 8px 16px; background: none; border: none; width: 100%; text-align: left;">Set {{ ucfirst($p) }}</button>
                    </form>
                    @endforeach
                </div>
            </div>

            @if($ticket->status !== 'closed')
            <!-- Assign to me -->
            @if(!$ticket->assigned_to || $ticket->assigned_to !== auth()->id())
            <form method="POST" action="{{ route('admin.support.assign-me', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); color: #3b82f6; border-radius: 10px; padding: 8px 16px; font-size: 12px;">
                    <i class="fas fa-user-plus"></i> Assign to Me
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.support.close', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px; padding: 8px 16px; font-size: 12px;" onclick="return confirm('Close this ticket?')">
                    <i class="fas fa-times-circle"></i> Close
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.support.reopen', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 10px; padding: 8px 16px; font-size: 12px;">
                    <i class="fas fa-redo"></i> Reopen
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

    <!-- Ticket Info -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <div class="row g-3" style="font-size: 13px;">
            <div class="col-md-2">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 12px;">
                        {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-bright); font-weight: 600;">{{ $ticket->user?->name ?? 'Unknown' }}</div>
                        <div style="color: var(--text-dim); font-size: 11px;">{{ $ticket->user?->email ?? '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <span style="color: var(--text-dim); font-size: 11px; display: block;">Category</span>
                <span style="color: #818cf8; text-transform: capitalize; font-weight: 600;">{{ $ticket->category }}</span>
            </div>
            <div class="col-md-2">
                <span style="color: var(--text-dim); font-size: 11px; display: block;">Priority</span>
                @php $pColors = ['low' => '#64748b', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444']; @endphp
                <span style="color: {{ $pColors[$ticket->priority] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
            </div>
            <div class="col-md-2">
                <span style="color: var(--text-dim); font-size: 11px; display: block;">Status</span>
                @php $sColors = ['open' => '#3b82f6', 'answered' => '#10b981', 'pending' => '#f59e0b', 'closed' => '#64748b']; @endphp
                <span style="color: {{ $sColors[$ticket->status] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->status }}</span>
            </div>
            <div class="col-md-2">
                <span style="color: var(--text-dim); font-size: 11px; display: block;">Assigned To</span>
                @if(isset($admins) && !$ticket->assigned_to)
                <select id="assignSelect" onchange="assignTicket({{ $ticket->id }}, this.value)" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 6px; padding: 2px 6px; font-size: 12px;">
                    <option value="">Unassigned</option>
                    @foreach($admins as $a)
                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
                @else
                <span style="color: var(--text-muted);">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</span>
                @endif
            </div>
            <div class="col-md-2">
                <span style="color: var(--text-dim); font-size: 11px; display: block;">Created</span>
                <span style="color: var(--text-muted);">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
        
        <!-- Rating display if exists -->
        @if($ticket->rating)
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 11px; color: var(--text-dim);">User Rating:</span>
            <span style="font-size: 16px; letter-spacing: 2px;">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $ticket->rating) ⭐ @else ☆ @endif
                @endfor
            </span>
            @if($ticket->rating_comment)
            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">"{{ $ticket->rating_comment }}"</span>
            @endif
        </div>
        @endif
    </div>

    <!-- Message Thread -->
    <div style="max-width:100%;max-width:800px;">
        @foreach($messages as $msg)
        @php $isUser = !$msg->is_admin; @endphp
        <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-direction: {{ $isUser ? 'row' : 'row-reverse' }};">
            <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 14px; background: {{ $msg->is_admin ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #6366f1, #7c3aed)' }};">
                {{ strtoupper(substr($msg->user?->name ?? 'S', 0, 1)) }}
            </div>
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

        <!-- Admin Reply Form -->
        @if($ticket->status !== 'closed')
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex gap-2">
                    <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 14px; background: linear-gradient(135deg, #ef4444, #dc2626);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="flex: 1;">
                        <textarea name="message" rows="4" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 14px; padding: 14px 18px; font-size: 14px; resize: vertical;" placeholder="Type your reply to the user..." required></textarea>
                        
                        <!-- Attachments -->
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                            <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.15); color: #818cf8; font-size: 12px;">
                                <i class="fas fa-paperclip"></i> Attach files
                                <input type="file" name="attachments[]" multiple style="display:none;" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip" onchange="updateFileNames(this)">
                            </label>
                            <span id="fileNames" style="font-size: 11px; color: var(--text-dim);"></span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div style="font-size: 11px; color: var(--text-dim);">
                                <i class="fas fa-info-circle"></i> Replying as {{ auth()->user()->name }} (Support Team)
                            </div>
                            <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 10px 24px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Quick Templates -->
            <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 11px; color: var(--text-dim); padding: 6px 0;">Quick replies:</span>
                <button type="button" onclick="setQuickReply('Thank you for reaching out. We have received your message and will look into this shortly.')" style="padding: 4px 10px; border-radius: 8px; background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.12); color: #818cf8; font-size: 11px; cursor: pointer;">Acknowledged</button>
                <button type="button" onclick="setQuickReply('Your deposit has been confirmed and credited to your account. Thank you for your patience.')" style="padding: 4px 10px; border-radius: 8px; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.12); color: #10b981; font-size: 11px; cursor: pointer;">Deposit Confirmed</button>
                <button type="button" onclick="setQuickReply('Your withdrawal has been processed. Please allow up to 24 hours for the funds to arrive in your account.')" style="padding: 4px 10px; border-radius: 8px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.12); color: #3b82f6; font-size: 11px; cursor: pointer;">Withdrawal Sent</button>
                <button type="button" onclick="setQuickReply('Could you please provide more details about the issue? Include any relevant transaction IDs, screenshots, or error messages.')" style="padding: 4px 10px; border-radius: 8px; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.12); color: #f59e0b; font-size: 11px; cursor: pointer;">Need More Info</button>
                <button type="button" onclick="setQuickReply('Thank you for your patience. This issue has been resolved. If you experience any further problems, please create a new ticket.')" style="padding: 4px 10px; border-radius: 8px; background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.12); color: #a855f7; font-size: 11px; cursor: pointer;">Resolved</button>
            </div>
        </div>
        @else
        <div style="text-align: center; padding: 30px; color: var(--text-dim); font-size: 14px;">
            <i class="fas fa-lock" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
            This ticket is closed. <a href="{{ route('admin.support.reopen', $ticket) }}" style="color: #818cf8; text-decoration: none;" onclick="event.preventDefault(); document.getElementById('reopen-form').submit();">Reopen it</a> to reply.
            <form id="reopen-form" method="POST" action="{{ route('admin.support.reopen', $ticket) }}" style="display:none;">@csrf</form>
        </div>
        @endif
    </div>
</div>

<script>
function setQuickReply(text) {
    document.querySelector('textarea[name=message]').value = text;
}

function updateFileNames(input) {
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        document.getElementById('fileNames').textContent = names;
    }
}

function assignTicket(ticketId, userId) {
    if (!userId) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/support/${ticketId}/assign`;
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    const assign = document.createElement('input');
    assign.type = 'hidden';
    assign.name = 'assigned_to';
    assign.value = userId;
    form.appendChild(csrf);
    form.appendChild(assign);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection