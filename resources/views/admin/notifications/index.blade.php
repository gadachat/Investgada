@extends('layouts.admin')

@section('page-title', 'Notifications — Admin')

@section('content')
<div class="fade-in">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px;">
                <i class="fas fa-bullhorn me-2" style="color: var(--purple-1);"></i>
                Notification Center
            </h2>
            <p style="color: var(--text-muted); margin: 0; font-size: 14px;">Send broadcasts and manage notification templates</p>
        </div>
        <div class="d-flex gap-3">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: var(--purple-1);">{{ $totalSent }}</div>
                <small style="color: var(--text-dim);">Total Sent</small>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #f59e0b;">{{ $unreadTotal }}</div>
                <small style="color: var(--text-dim);">Unread</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Broadcast Form -->
        <div class="col-lg-5 col-md-6 col-12">
            <div class="admin-card p-4 mb-3">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-paper-plane me-2" style="color: var(--purple-2);"></i>
                    Send Broadcast
                </h5>
                <form action="{{ route('admin.notifications.broadcast') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Target Audience</label>
                        <select name="target" class="form-select" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                            <option value="all">All Users ({{ $userCount }})</option>
                            <option value="active">Active Users Only ({{ $activeUsers }})</option>
                            <option value="inactive">Inactive Users</option>
                            <option value="investors">Active Investors</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Notification Type</label>
                        <select name="type" class="form-select" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                            <option value="system">System</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="investment">Investment</option>
                            <option value="referral">Referral</option>
                            <option value="profit">Profit Share</option>
                            <option value="kyc">KYC</option>
                            <option value="support">Support</option>
                            <option value="rank">Rank Update</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Title</label>
                        <input type="text" name="title" class="form-control" required maxlength="255"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               placeholder="Important announcement...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Message</label>
                        <textarea name="message" class="form-control" rows="4" required maxlength="2000"
                                  style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                                  placeholder="Write your notification message..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Link (optional)</label>
                        <input type="url" name="link" class="form-control"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               placeholder="https://...">
                    </div>
                    <button type="submit" class="btn btn-gradient w-100">
                        <i class="fas fa-send me-1"></i> Send Broadcast
                    </button>
                </form>
            </div>

            <!-- Templates -->
            <div class="admin-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 style="color: var(--text-bright); font-weight: 600; margin: 0;">
                        <i class="fas fa-bookmark me-2" style="color: var(--purple-1);"></i>
                        Templates
                    </h6>
                    <button class="btn btn-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                            data-bs-toggle="modal" data-bs-target="#templateModal">
                        <i class="fas fa-plus"></i> New
                    </button>
                </div>
                @forelse($templates as $tpl)
                <div class="d-flex justify-content-between align-items-center" style="padding: 10px; border-radius: 8px; background: var(--bg-input); margin-bottom: 6px;">
                    <div>
                        <div style="color: var(--text-bright); font-weight: 600; font-size: 13px;">{{ $tpl->name }}</div>
                        <small style="color: var(--text-dim);">{{ $tpl->title }}</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm py-0 px-2" style="background: var(--purple-1); color: white; font-size: 11px;"
                                onclick="useTemplate('{{ addslashes($tpl->title) }}', '{{ addslashes($tpl->message) }}', '{{ $tpl->type }}')">
                            Use
                        </button>
                        <form action="{{ route('admin.notifications.delete-template', $tpl->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm py-0 px-2" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text-dim); font-size: 11px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p style="color: var(--text-dim); font-size: 13px; text-align: center;">No templates yet</p>
                @endforelse
            </div>
        </div>

        <!-- All Notifications -->
        <div class="col-lg-7">
            <div class="admin-card p-4">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-list me-2" style="color: var(--purple-1);"></i>
                    All Notifications
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover" style="color: var(--text);">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Type</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Title</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Date</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Status</th>
                                <th style="border: none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $n)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="border: none;">
                                    <span class="badge" style="background: var(--purple-1); color: white; font-size: 10px; text-transform: capitalize;">
                                        {{ $n->type }}
                                    </span>
                                </td>
                                <td style="border: none; font-size: 13px; color: var(--text-bright); font-weight: 500;">
                                    {{ $n->title }}
                                    <br><small style="color: var(--text-dim);">{{ \Str::limit($n->message, 60) }}</small>
                                </td>
                                <td style="border: none; font-size: 12px; color: var(--text-muted);">
                                    {{ \Carbon\Carbon::parse($n->created_at)->format('M j, Y g:i A') }}
                                </td>
                                <td style="border: none;">
                                    @if($n->is_read)
                                    <span class="badge" style="background: rgba(99, 102, 241, 0.15); color: var(--purple-1);">Read</span>
                                    @else
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Unread</span>
                                    @endif
                                </td>
                                <td style="border: none;">
                                    <form action="{{ route('admin.notifications.destroy', $n->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--text-dim); cursor: pointer;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="border: none; text-align: center; padding: 40px; color: var(--text-muted);">No notifications</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="color: var(--text-bright);">Save Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.notifications.store-template') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Template Name</label>
                        <input type="text" name="name" class="form-control" required
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               placeholder="e.g. Welcome message">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Type</label>
                        <select name="type" class="form-select" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                            <option value="system">System</option>
                            <option value="deposit">Deposit</option>
                            <option value="profit">Profit</option>
                            <option value="kyc">KYC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Title</label>
                        <input type="text" name="title" class="form-control" required
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Message</label>
                        <textarea name="message" class="form-control" rows="3" required
                                  style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text);" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function useTemplate(title, message, type) {
    document.querySelector('input[name="title"]').value = title;
    document.querySelector('textarea[name="message"]').value = message;
    document.querySelector('select[name="type"]').value = type;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endsection
