@php
    $isRoot = $isRoot ?? false;
    $isLeft = ($node['position'] ?? 'left') === 'left';
    $accentColor = $isRoot ? '#6366f1' : ($isLeft ? '#3b82f6' : '#a855f7');
    $gradient = $isRoot
        ? 'linear-gradient(135deg, #6366f1, #a855f7)'
        : ($isLeft
            ? 'linear-gradient(135deg, #3b82f6, #2563eb)'
            : 'linear-gradient(135deg, #a855f7, #7c3aed)');
    $borderColor = $accentColor . '40';
@endphp

<div style="display: flex; flex-direction: column; align-items: center; padding: 0 12px;">

    @unless($isRoot)
    <div style="width: 2px; height: 24px; background: {{ $accentColor }}; opacity: 0.4;"></div>
    @endunless

    <!-- Node Card -->
    <div style="text-align: center; position: relative;">
        <div style="padding: 14px 18px; border-radius: 14px; background: {{ $isRoot ? 'linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-2) 100%)' : 'var(--bg-card-2)' }};
                    border: {{ $isRoot ? '2px solid ' . $accentColor : '1px solid ' . $borderColor }};
                    min-width: 150px; box-shadow: {{ $isRoot ? '0 4px 24px ' . $accentColor . '30' : 'none' }};
                    transition: all 0.2s;"
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px {{ $accentColor }}30'"
             onmouseout="this.style.transform=''; this.style.boxShadow='{{ $isRoot ? '0 4px 24px ' . $accentColor . '30' : 'none' }}'">

            <!-- Avatar -->
            <div style="width: 44px; height: 44px; border-radius: 50%; background: {{ $gradient }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px; margin: 0 auto 8px; box-shadow: 0 2px 8px {{ $accentColor }}40;">
                @if($node['avatar'])
                    <img src="{{ $node['avatar'] }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                @else
                    {{ strtoupper(substr($node['name'] ?? '?', 0, 1)) }}
                @endif
            </div>

            <!-- Name & Rank -->
            <div style="color: var(--text-bright); font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                {{ $node['name'] ?? 'Available' }}
            </div>
            <div style="color: {{ $accentColor }}; font-size: 11px; font-weight: 600; margin-top: 2px;">
                {{ $node['rank'] ?? 'Empty' }}
            </div>

            <!-- Volume -->
            @if(isset($node['volume']) && $node['volume'] > 0)
            <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(255,255,255,0.06);">
                <div style="color: var(--text-muted); font-size: 10px;">Volume</div>
                <div style="color: var(--text-bright); font-weight: 600; font-size: 12px;">${{ number_format($node['volume'], 0) }}</div>
            </div>
            @endif

            <!-- Status indicator -->
            @if(isset($node['isActive']) && !$node['isActive'])
            <div style="margin-top: 4px;">
                <span style="background: rgba(100,116,139,0.2); color: var(--text-dim); padding: 1px 8px; border-radius: 8px; font-size: 9px;">INACTIVE</span>
            </div>
            @endif
        </div>

        @if($isRoot)
        <!-- Position badge for root -->
        <div style="position: absolute; top: -6px; right: -8px;">
            <span style="background: {{ $gradient }}; color: white; padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;">YOU</span>
        </div>
        @endif
    </div>

    @php
        $hasLeft = $node['hasLeft'] ?? false;
        $hasRight = $node['hasRight'] ?? false;
        $leftNode = $node['left'] ?? null;
        $rightNode = $node['right'] ?? null;
    @endphp

    @if($hasLeft || $hasRight)
    <!-- Vertical connector -->
    <div style="width: 2px; height: 20px; background: var(--border);"></div>

    <!-- Children container -->
    <div style="display: flex; gap: 20px; position: relative;">

        <!-- Horizontal connector line -->
        @if($hasLeft && $hasRight)
        <div style="position: absolute; top: 0; left: 50%; right: 50%; height: 2px; background: var(--border); transform: translateX(-50%); width: 100%;"></div>
        @endif

        <!-- Left child -->
        <div style="display: flex; flex-direction: column; align-items: center;">
            @if($hasLeft && $leftNode)
                @include('dashboard.binary._tree-node', ['node' => $leftNode, 'isRoot' => false])
            @else
                <!-- Empty slot -->
                <div style="width: 2px; height: 24px; background: #3b82f6; opacity: 0.3;"></div>
                <div style="text-align: center; padding: 14px 18px; border-radius: 14px; background: transparent; border: 2px dashed rgba(59,130,246,0.3); min-width: 140px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(59,130,246,0.05); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                        <i class="fas fa-plus" style="color: rgba(59,130,246,0.4); font-size: 16px;"></i>
                    </div>
                    <div style="color: var(--text-dim); font-size: 12px; font-weight: 500;">Empty Slot</div>
                    <div style="color: var(--text-dim); font-size: 10px;">Left Position</div>
                </div>
            @endif
        </div>

        <!-- Right child -->
        <div style="display: flex; flex-direction: column; align-items: center;">
            @if($hasRight && $rightNode)
                @include('dashboard.binary._tree-node', ['node' => $rightNode, 'isRoot' => false])
            @else
                <!-- Empty slot -->
                <div style="width: 2px; height: 24px; background: #a855f7; opacity: 0.3;"></div>
                <div style="text-align: center; padding: 14px 18px; border-radius: 14px; background: transparent; border: 2px dashed rgba(168,85,247,0.3); min-width: 140px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(168,85,247,0.05); display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                        <i class="fas fa-plus" style="color: rgba(168,85,247,0.4); font-size: 16px;"></i>
                    </div>
                    <div style="color: var(--text-dim); font-size: 12px; font-weight: 500;">Empty Slot</div>
                    <div style="color: var(--text-dim); font-size: 10px;">Right Position</div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
