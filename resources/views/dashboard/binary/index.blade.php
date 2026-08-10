@extends('layouts.dashboard')

@section('page-title', 'Binary Tree')

@section('content')
<div class="fade-in">

    <!-- ========== HEADER ========== -->
    <div style="background: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #a855f7 100%); border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -30px; right: -30px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); border-radius: 50%;"></div>
        <div style="position: relative; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="color: white; font-weight: 800; margin: 0 0 4px; font-size: 24px;">
                    <i class="fas fa-sitemap" style="margin-right: 8px;"></i> Binary Network Tree
                </h2>
                <p style="color: rgba(255,255,255,0.75); margin: 0; font-size: 13px;">
                    {{ $currentCycle['label'] }} · {{ $matchingFrequency }} matching · Rank: {{ $rank }}
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <div style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 10px; text-align: center;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase;">Cycle Earnings</div>
                    <div style="color: white; font-weight: 700; font-size: 18px;">${{ number_format($cycleEarnings['earnings'], 2) }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 10px; text-align: center;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase;">Downline</div>
                    <div style="color: white; font-weight: 700; font-size: 18px;">{{ $totalDownline }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== LEG COMPARISON + MATCHING ========== -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">

        <!-- Left Leg Card -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 20px; border: 1px solid var(--border); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #3b82f6, #2563eb);"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-left" style="color: white; font-size: 14px;"></i>
                    </div>
                    <span style="font-weight: 700; color: var(--text-bright); font-size: 15px;">Left Leg</span>
                </div>
                @if($weakLeg == 'left')
                <span style="background: rgba(168,85,247,0.15); color: #c084fc; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">WEAK</span>
                @else
                <span style="background: rgba(59,130,246,0.15); color: #60a5fa; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">STRONG</span>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <div style="color: var(--text-muted); font-size: 11px;">Members</div>
                    <div style="color: var(--text-bright); font-weight: 700; font-size: 18px;">{{ $leftLeg['count'] }}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 11px;">Active</div>
                    <div style="color: #34d399; font-weight: 700; font-size: 18px;">{{ $leftLeg['active'] }}</div>
                </div>
            </div>
            <div style="padding-top: 12px; border-top: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--text-muted); font-size: 11px;">Volume</span>
                    <span style="color: var(--text-bright); font-weight: 600; font-size: 13px;">${{ number_format($leftLeg['volume'] + $leftCarryForward, 2) }}</span>
                </div>
                @if($leftCarryForward > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim); font-size: 10px;">Carry Forward</span>
                    <span style="color: #60a5fa; font-size: 11px;">${{ number_format($leftCarryForward, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Matching Summary Card -->
        <div style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-2) 100%); border-radius: 14px; padding: 20px; border: 1px solid rgba(99,102,241,0.3); text-align: center;">
            <div style="margin-bottom: 14px;">
                <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Potential Matching Bonus</div>
                <div style="font-size: 32px; font-weight: 800; background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">${{ number_format($potentialBonus, 2) }}</div>
            </div>
            <div style="display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div style="text-align: center;">
                    <div style="color: #60a5fa; font-weight: 700; font-size: 16px;">${{ number_format($leftLeg['volume'] + $leftCarryForward, 0) }}</div>
                    <div style="color: var(--text-muted); font-size: 10px;">Left</div>
                </div>
                <i class="fas fa-balance-scale" style="color: var(--purple-1); font-size: 18px;"></i>
                <div style="text-align: center;">
                    <div style="color: #c084fc; font-weight: 700; font-size: 16px;">${{ number_format($rightLeg['volume'] + $rightCarryForward, 0) }}</div>
                    <div style="color: var(--text-muted); font-size: 10px;">Right</div>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); padding-top: 10px; border-top: 1px solid var(--border);">
                Matched Volume: <span style="color: var(--text-bright); font-weight: 600;">${{ number_format($weakVol, 2) }}</span> ·
                Rate: <span style="color: var(--purple-1); font-weight: 600;">{{ $matchingBonusRate }}%</span> ·
                Cap: <span style="color: var(--text-bright); font-weight: 600;">${{ number_format($matchingBonusCap, 0) }}</span>
            </div>
        </div>

        <!-- Right Leg Card -->
        <div style="background: var(--bg-card); border-radius: 14px; padding: 20px; border: 1px solid var(--border); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #a855f7, #7c3aed);"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-right" style="color: white; font-size: 14px;"></i>
                    </div>
                    <span style="font-weight: 700; color: var(--text-bright); font-size: 15px;">Right Leg</span>
                </div>
                @if($weakLeg == 'right')
                <span style="background: rgba(168,85,247,0.15); color: #c084fc; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">WEAK</span>
                @else
                <span style="background: rgba(168,85,247,0.15); color: #c084fc; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">STRONG</span>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <div style="color: var(--text-muted); font-size: 11px;">Members</div>
                    <div style="color: var(--text-bright); font-weight: 700; font-size: 18px;">{{ $rightLeg['count'] }}</div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 11px;">Active</div>
                    <div style="color: #34d399; font-weight: 700; font-size: 18px;">{{ $rightLeg['active'] }}</div>
                </div>
            </div>
            <div style="padding-top: 12px; border-top: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--text-muted); font-size: 11px;">Volume</span>
                    <span style="color: var(--text-bright); font-weight: 600; font-size: 13px;">${{ number_format($rightLeg['volume'] + $rightCarryForward, 2) }}</span>
                </div>
                @if($rightCarryForward > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim); font-size: 10px;">Carry Forward</span>
                    <span style="color: #c084fc; font-size: 11px;">${{ number_format($rightCarryForward, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========== BINARY TREE VISUALIZATION ========== -->
    <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 16px;">
                <i class="fas fa-project-diagram" style="color: var(--purple-1); margin-right: 6px;"></i>
                Genealogy Tree
            </h5>
            <div style="display: flex; gap: 8px;">
                <button onclick="expandAllNodes()" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text-muted); padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 12px;">
                    <i class="fas fa-expand-arrows-alt"></i> Expand All
                </button>
                <button onclick="collapseAllNodes()" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text-muted); padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 12px;">
                    <i class="fas fa-compress-arrows-alt"></i> Collapse
                </button>
            </div>
        </div>

        <!-- Tree Canvas -->
        <div id="treeCanvas" style="overflow-x: auto; padding: 20px 0; min-height: 400px;">
            <div id="treeContainer" style="display: flex; justify-content: center; min-width: fit-content;">
                @include('dashboard.binary._tree-node', ['node' => $tree, 'isRoot' => true])
            </div>
        </div>

        <!-- Legend -->
        <div style="display: flex; justify-content: center; gap: 20px; padding-top: 16px; border-top: 1px solid var(--border); flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #a855f7);"></div>
                You (Root)
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>
                Left Leg
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: linear-gradient(135deg, #a855f7, #7c3aed);"></div>
                Right Leg
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted);">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: rgba(100,116,139,0.3); border: 2px dashed var(--text-dim);"></div>
                Available Slot
            </div>
        </div>
    </div>

    <!-- ========== MATCHING BONUS HISTORY ========== -->
    <div style="background: var(--bg-card); border-radius: 14px; padding: 20px; border: 1px solid var(--border); margin-bottom: 24px;">
        <h5 style="color: var(--text-bright); font-weight: 700; margin: 0 0 16px; font-size: 16px;">
            <i class="fas fa-balance-scale" style="color: var(--purple-3); margin-right: 6px;"></i>
            Matching Bonus History
            <span style="color: var(--text-muted); font-size: 13px; font-weight: 400; margin-left: 8px;">({{ $matchingFrequency }} · flush: {{ $flushOutPeriod }})</span>
        </h5>

        @if($matchingHistory->isEmpty())
            <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                <i class="fas fa-balance-scale" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                <p style="font-size: 14px;">No matching bonuses earned yet. Build both legs to qualify.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <th style="text-align: left; padding: 10px; color: var(--text-muted); font-size: 12px; font-weight: 600;">Date</th>
                            <th style="text-align: left; padding: 10px; color: var(--text-muted); font-size: 12px; font-weight: 600;">Left Volume</th>
                            <th style="text-align: left; padding: 10px; color: var(--text-muted); font-size: 12px; font-weight: 600;">Right Volume</th>
                            <th style="text-align: left; padding: 10px; color: var(--text-muted); font-size: 12px; font-weight: 600;">Matched</th>
                            <th style="text-align: right; padding: 10px; color: var(--text-muted); font-size: 12px; font-weight: 600;">Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matchingHistory as $record)
                        <tr style="border-bottom: 1px solid rgba(30,41,59,0.5);">
                            <td style="padding: 12px 10px; color: var(--text-muted); font-size: 13px;">{{ $record->created_date?->format('M d, Y H:i') }}</td>
                            <td style="padding: 12px 10px; color: #60a5fa; font-size: 13px;">${{ number_format($record->metadata['left_volume'] ?? 0, 2) }}</td>
                            <td style="padding: 12px 10px; color: #c084fc; font-size: 13px;">${{ number_format($record->metadata['right_volume'] ?? 0, 2) }}</td>
                            <td style="padding: 12px 10px; color: var(--text-bright); font-size: 13px; font-weight: 600;">${{ number_format($record->metadata['matched_volume'] ?? 0, 2) }}</td>
                            <td style="padding: 12px 10px; text-align: right; color: #34d399; font-weight: 700; font-size: 14px;">+${{ number_format($record->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<script>
// Tree node expansion via AJAX
async function toggleNode(nodeId, button) {
    const childrenContainer = document.getElementById('children-' + nodeId);

    if (childrenContainer.style.display === 'none') {
        // Check if already loaded
        if (!childrenContainer.dataset.loaded) {
            try {
                const response = await fetch('/dashboard/binary/node/' + nodeId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }

                // Render children
                childrenContainer.innerHTML = renderChildren(data.children);
                childrenContainer.dataset.loaded = 'true';
            } catch (e) {
                showToast('Failed to load tree data', 'error');
                return;
            }
        }
        childrenContainer.style.display = 'flex';
        button.innerHTML = '<i class="fas fa-minus"></i>';
    } else {
        childrenContainer.style.display = 'none';
        button.innerHTML = '<i class="fas fa-plus"></i>';
    }
}

function renderChildren(children) {
    let html = '';
    children.forEach(child => {
        const isLeft = child.position === 'left';
        const grad = isLeft ? 'linear-gradient(135deg, #3b82f6, #2563eb)' : 'linear-gradient(135deg, #a855f7, #7c3aed)';
        const accent = isLeft ? '#3b82f6' : '#a855f7';

        html += `
        <div style="display: flex; flex-direction: column; align-items: center; padding: 0 8px;">
            <div style="width: 2px; height: 20px; background: ${accent}; opacity: 0.5;"></div>
            <div style="text-align: center; padding: 12px 16px; border-radius: 12px; background: var(--bg-card-2); border: 1px solid ${accent}40; min-width: 140px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: ${grad}; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin: 0 auto 6px;">
                    ${child.name.charAt(0).toUpperCase()}
                </div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 12px;">${child.name}</div>
                <div style="color: var(--text-muted); font-size: 10px;">${child.rank}</div>
                <div style="color: ${accent}; font-size: 11px; font-weight: 600; margin-top: 4px;">$${formatNum(child.volume)}</div>
                ${child.hasChildren ? `<button onclick="toggleNode(${child.id}, this)" style="margin-top: 6px; background: ${accent}20; border: none; color: ${accent}; padding: 2px 8px; border-radius: 6px; cursor: pointer; font-size: 10px;"><i class="fas fa-plus"></i></button>` : ''}
            </div>
        </div>`;
    });
    return html;
}

function expandAllNodes() {
    document.querySelectorAll('[id^="children-"]').forEach(el => {
        el.style.display = 'flex';
    });
    document.querySelectorAll('[onclick^="toggleNode"]').forEach(btn => {
        if (btn.innerHTML.includes('plus')) btn.innerHTML = '<i class="fas fa-minus"></i>';
    });
}

function collapseAllNodes() {
    document.querySelectorAll('[id^="children-"]').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('[onclick^="toggleNode"]').forEach(btn => {
        if (btn.innerHTML.includes('minus')) btn.innerHTML = '<i class="fas fa-plus"></i>';
    });
}

function formatNum(n) {
    return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endsection
