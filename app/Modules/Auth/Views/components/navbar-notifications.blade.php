@php
    $dropdownId = $dropdownId ?? 'mmhcNotificationDropdown';
    $items = $mmhcRecentNotifications ?? collect();
    $unread = (int) ($mmhcUnreadNotificationsCount ?? $communityUnreadNotificationsCount ?? 0);
@endphp
<div class="dropdown mmhc-nav-notifications">
    <button type="button"
            class="{{ $buttonClass ?? 'mmhc-nav-notifications-btn' }}"
            id="{{ $dropdownId }}"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Notifications">
        <i class="fas fa-bell"></i>
        @if($unread > 0)
            <span class="mmhc-nav-notifications-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end mmhc-nav-notifications-menu shadow" aria-labelledby="{{ $dropdownId }}">
        <li class="dropdown-header d-flex justify-content-between align-items-center gap-2">
            <span>Notifications</span>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
            </form>
        </li>
        @forelse($items as $item)
            <li>
                <form method="POST" action="{{ route($item['open_route'], $item['open_params']) }}">
                    @csrf
                    @if(($item['source'] ?? '') === 'community')
                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    @endif
                    <button type="submit" class="dropdown-item small text-start w-100 border-0 bg-transparent mmhc-notif-item @if(!empty($item['unread'])) is-unread fw-semibold @endif">
                        <div class="d-flex gap-2 align-items-start">
                            <span class="mmhc-notif-item__icon" aria-hidden="true">
                                <i class="fas {{ $item['icon'] ?? 'fa-bell' }}"></i>
                            </span>
                            <span class="min-w-0 flex-grow-1">
                                <span class="d-block">{{ $item['title'] }}</span>
                                @if(!empty($item['body']))
                                    <span class="d-block text-muted fw-normal mmhc-notif-item__body">{{ \Illuminate\Support\Str::limit($item['body'], 90) }}</span>
                                @endif
                                <span class="d-block text-muted fw-normal">{{ optional($item['created_at'])->diffForHumans() }}</span>
                            </span>
                        </div>
                    </button>
                </form>
            </li>
        @empty
            <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
        @endforelse
    </ul>
</div>
