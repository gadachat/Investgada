@extends('layouts.dashboard')

@section('page-title', 'Referral System')

@section('content')
<div class="fade-in">

    <!-- ========== HERO: Referral Link & Summary ========== -->
    <div style="background: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #a855f7 100%); border-radius: 16px; padding: 28px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -30px; right: -30px; width:100%;max-width:250px; height: 250px; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -50px; right: 100px; width:100%;max-width:180px; height: 180px; background: radial-gradient(circle, rgba(168,85,247,0.15) 0%, transparent 70%); border-radius: 50%;"></div>

        <div style="position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
                <div>
                    <h2 style="color: white; font-weight: 800; margin: 0 0 6px; font-size: 26px;">
                        <i class="fas fa-users" style="margin-right: 8px;"></i> Referral Network
                    </h2>
                    <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">
                        Invite traders and earn commissions on every investment they make.
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 28px; font-weight: 800; color: white;">${{ number_format($referralEarnings + $matchingBonus, 2) }}</div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 13px;">Total Referral Earnings</div>
                </div>
            </div>

            <!-- Referral Link Card -->
            <div style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                        <div style="color: rgba(255,255,255,0.7); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Your Referral Link</div>
                        <div style="color: white; font-weight: 600; font-size: 15px; word-break: break-all;" id="referralLink">{{ $referralLink }}</div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        <button onclick="copyReferralLink()" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <button onclick="shareLink('whatsapp')" style="background: #25D366; border: none; color: white; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                        <button onclick="shareLink('telegram')" style="background: #0088cc; border: none; color: white; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <i class="fab fa-telegram"></i>
                        </button>
                        <button onclick="shareLink('email')" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </div>
                </div>

                <!-- Referral code -->
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="color: rgba(255,255,255,0.6); font-size: 13px;">Or share your code:</div>
                    <div style="background: rgba(0,0,0,0.3); padding: 6px 16px; border-radius: 8px; color: white; font-family: monospace; font-weight: 700; font-size: 16px; letter-spacing: 2px;">{{ auth()->user()->referral_code }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== STAT CARDS ========== -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">

        <!-- Total Referrals -->
        <div style="background: var(--bg-card); border-radius: 12px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Total Referrals</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-bright);">{{ $totalReferrals }}</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-plus" style="color: white; font-size: 18px;"></i>
                </div>
            </div>
            <div style="margin-top: 12px; display: flex; gap: 16px; font-size: 12px;">
                <span style="color: #34d399;"><i class="fas fa-circle" style="font-size: 8px;"></i> {{ $activeReferrals }} Active</span>
                <span style="color: var(--text-dim);"><i class="fas fa-circle" style="font-size: 8px;"></i> {{ $inactiveReferrals }} Inactive</span>
            </div>
        </div>

        <!-- Direct Commission -->
        <div style="background: var(--bg-card); border-radius: 12px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Direct Commission</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-bright);">${{ number_format($referralEarnings, 2) }}</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-handshake" style="color: white; font-size: 18px;"></i>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 12px; color: var(--text-muted);">
                @{{ $directCommissionRate }}% on each direct referral's investment
            </div>
        </div>

        <!-- Matching Bonus -->
        <div style="background: var(--bg-card); border-radius: 12px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Matching Bonus</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-bright);">${{ number_format($matchingBonus, 2) }}</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #a855f7, #6366f1); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-balance-scale" style="color: white; font-size: 18px;"></i>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 12px; color: var(--text-muted);">
                @{{ $matchingBonusRate }}% of matched volume · Cap: ${{ number_format($matchingBonusCap, 0) }}
            </div>
        </div>

        <!-- Total Downline -->
        <div style="background: var(--bg-card); border-radius: 12px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Total Downline</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-bright);">{{ $totalDownline }}</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #a855f7); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-sitemap" style="color: white; font-size: 18px;"></i>
                </div>
            </div>
            <div style="margin-top: 12px; display: flex; gap: 16px; font-size: 12px;">
                <span style="color: #3b82f6;"><i class="fas fa-arrow-left" style="font-size: 10px;"></i> Left: {{ $leftLegCount }}</span>
                <span style="color: #a855f7;"><i class="fas fa-arrow-right" style="font-size: 10px;"></i> Right: {{ $rightLegCount }}</span>
            </div>
        </div>
    </div>

    <!-- ========== BINARY LEG SUMMARY ========== -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">

        <!-- Left Leg -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 22px; border: 1px solid var(--border); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #3b82f6, #2563eb);"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-left" style="color: white; font-size: 16px;"></i>
                    </div>
                    <div style="font-weight: 700; color: var(--text-bright); font-size: 16px;">Left Leg</div>
                </div>
                <div style="background: rgba(59,130,246,0.15); color: #60a5fa; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    {{ $leftLegCount }} members
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Volume</div>
                    <div style="font-size: 20px; font-weight: 700; color: var(--text-bright);">${{ number_format($leftLegVolume, 2) }}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Carry Forward</div>
                    <div style="font-size: 20px; font-weight: 700; color: #60a5fa;">${{ number_format($leftCarryForward, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Right Leg -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 22px; border: 1px solid var(--border); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #a855f7, #7c3aed);"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-right" style="color: white; font-size: 16px;"></i>
                    </div>
                    <div style="font-weight: 700; color: var(--text-bright); font-size: 16px;">Right Leg</div>
                </div>
                <div style="background: rgba(168,85,247,0.15); color: #c084fc; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    {{ $rightLegCount }} members
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Volume</div>
                    <div style="font-size: 20px; font-weight: 700; color: var(--text-bright);">${{ number_format($rightLegVolume, 2) }}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Carry Forward</div>
                    <div style="font-size: 20px; font-weight: 700; color: #c084fc;">${{ number_format($rightCarryForward, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TWO COLUMN: Direct Referrals + Commission History ========== -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;" class="responsive-grid">

        <!-- Direct Referrals List -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 16px;">
                    <i class="fas fa-user-check" style="color: var(--purple-1); margin-right: 6px;"></i>
                    Direct Referrals ({{ $totalReferrals }})
                </h5>
                <a href="{{ route('dashboard.binary.index') }}" style="color: var(--purple-1); font-size: 13px; text-decoration: none;">
                    View Tree <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                </a>
            </div>

            @if($directReferrals->isEmpty())
                <div style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                    <i class="fas fa-user-plus" style="font-size: 32px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 14px;">No referrals yet. Share your link to start earning!</p>
                </div>
            @else
                <div style="max-height: 400px; overflow-y: auto; margin: 0 -8px; padding: 0 8px;">
                    @foreach($directReferrals as $referral)
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; margin-bottom: 8px; background: var(--bg-card-2); transition: all 0.2s;" onmouseover="this.style.background='var(--bg-input)'" onmouseout="this.style.background='var(--bg-card-2)'">
                        <!-- Avatar -->
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #a855f7); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; flex-shrink: 0;">
                            {{ strtoupper(substr($referral->name, 0, 1)) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--text-bright); font-weight: 600; font-size: 14px;">{{ $referral->name }}</span>
                                @if($referral->activeInvestments->isNotEmpty())
                                <span style="background: rgba(52,211,153,0.15); color: #34d399; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600;">ACTIVE</span>
                                @else
                                <span style="background: rgba(100,116,139,0.15); color: var(--text-dim); padding: 2px 8px; border-radius: 10px; font-size: 10px;">INACTIVE</span>
                                @endif
                            </div>
                            <div style="color: var(--text-muted); font-size: 12px;">{{ $referral->email }} · Joined {{ $referral->created_date?->format('M d, Y') }}</div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="color: var(--text-bright); font-weight: 600; font-size: 14px;">${{ number_format($referral->total_invested ?? 0, 2) }}</div>
                            <div style="color: var(--text-muted); font-size: 11px;">{{ $referral->position ? ucfirst($referral->position) : '—' }} leg</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Commission History -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 16px;">
                    <i class="fas fa-coins" style="color: var(--blue-1); margin-right: 6px;"></i>
                    Commission History
                </h5>
                <span style="color: var(--text-muted); font-size: 13px;">This week: ${{ number_format($weekEarnings, 2) }}</span>
            </div>

            @if($recentCommissions->isEmpty())
                <div style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                    <i class="fas fa-receipt" style="font-size: 32px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 14px;">No commissions yet. Your earnings will appear here.</p>
                </div>
            @else
                <div style="max-height: 400px; overflow-y: auto; margin: 0 -8px; padding: 0 8px;">
                    @foreach($recentCommissions as $commission)
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; margin-bottom: 8px; background: var(--bg-card-2);">
                        <div style="width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
                            {{ $commission->type == 'matching_bonus' ? 'background: rgba(168,85,247,0.15);' : 'background: rgba(99,102,241,0.15);' }}">
                            <i class="fas {{ $commission->type == 'matching_bonus' ? 'fa-balance-scale' : 'fa-handshake' }}"
                               style="color: {{ $commission->type == 'matching_bonus' ? '#c084fc' : '#818cf8' }}; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="color: var(--text-bright); font-weight: 600; font-size: 14px;">
                                {{ $commission->type == 'matching_bonus' ? 'Matching Bonus' : 'Direct Referral Commission' }}
                            </div>
                            <div style="color: var(--text-muted); font-size: 12px;">
                                {{ $commission->created_date?->format('M d, Y \a\t H:i') }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #34d399; font-weight: 700; font-size: 14px;">+${{ number_format($commission->amount, 2) }}</div>
                            @if(isset($commission->metadata['matched_volume']))
                            <div style="color: var(--text-muted); font-size: 11px;">Matched: ${{ number_format($commission->metadata['matched_volume'], 0) }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ========== How It Works ========== -->
    <div style="background: var(--bg-card); border-radius: 14px; padding: 24px; border: 1px solid var(--border); margin-bottom: 24px;">
        <h5 style="color: var(--text-bright); font-weight: 700; margin: 0 0 20px; font-size: 16px;">
            <i class="fas fa-info-circle" style="color: var(--purple-1); margin-right: 6px;"></i>
            How Referral Earnings Work
        </h5>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div style="padding: 16px; border-radius: 12px; background: var(--bg-card-2); border-left: 3px solid var(--purple-1);">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: white; font-weight: 700; font-size: 14px;">1</span>
                </div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 14px; margin-bottom: 4px;">Share Your Link</div>
                <div style="color: var(--text-muted); font-size: 12px;">Send your referral link to friends via social media or direct message.</div>
            </div>
            <div style="padding: 16px; border-radius: 12px; background: var(--bg-card-2); border-left: 3px solid var(--blue-1);">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: white; font-weight: 700; font-size: 14px;">2</span>
                </div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 14px; margin-bottom: 4px;">They Register & Invest</div>
                <div style="color: var(--text-muted); font-size: 12px;">When they sign up using your link and make their first investment.</div>
            </div>
            <div style="padding: 16px; border-radius: 12px; background: var(--bg-card-2); border-left: 3px solid var(--purple-3);">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: white; font-weight: 700; font-size: 14px;">3</span>
                </div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 14px; margin-bottom: 4px;">You Earn {{ $directCommissionRate }}% Commission</div>
                <div style="color: var(--text-muted); font-size: 12px;">Get direct commission on every investment your referral makes.</div>
            </div>
            <div style="padding: 16px; border-radius: 12px; background: var(--bg-card-2); border-left: 3px solid var(--blue-2);">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #2563eb, #6366f1); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="color: white; font-weight: 700; font-size: 14px;">4</span>
                </div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 14px; margin-bottom: 4px;">Matching Bonus</div>
                <div style="color: var(--text-muted); font-size: 12px;">Earn {{ $matchingBonusRate }}% on matched volume between your binary legs.</div>
            </div>
        </div>
    </div>

</div>

<script>
function copyReferralLink() {
    const link = document.getElementById('referralLink').textContent;
    navigator.clipboard.writeText(link).then(() => {
        // Show toast
        showToast('Referral link copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Referral link copied!', 'success');
    });
}

function shareLink(platform) {
    const link = document.getElementById('referralLink').textContent;
    const text = 'Join me on this amazing investment platform! 🚀';
    let url;

    switch(platform) {
        case 'whatsapp':
            url = 'https://wa.me/?text=' + encodeURIComponent(text + ' ' + link);
            break;
        case 'telegram':
            url = 'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent(text);
            break;
        case 'email':
            url = 'mailto:?subject=' + encodeURIComponent('Join me on this investment platform') + '&body=' + encodeURIComponent(text + '\n\n' + link);
            break;
    }

    window.open(url, '_blank');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed; top:20px; right:20px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; z-index: 9999; box-shadow: 0 4px 20px rgba(99,102,241,0.4); animation: fadeInDown 0.3s ease;';
    toast.innerHTML = '<i class="fas fa-check-circle" style="margin-right: 8px;"></i>' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}
</script>

@push('styles')
<style>
.responsive-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@@media (max-width:100%;max-width:992px) { .responsive-grid { grid-template-columns: 1fr; } }
@@media (max-width:100%;max-width:576px) { .responsive-grid { grid-template-columns: 1fr; } }
</style>
@endpush
@endsection
