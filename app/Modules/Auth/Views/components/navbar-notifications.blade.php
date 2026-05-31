@php
    $dropdownId = $dropdownId ?? 'communityNotificationDropdown';
@endphp
<div class="dropdown mmhc-nav-notifications">
    <button type="button"
            class="{{ $buttonClass ?? 'mmhc-nav-notifications-btn' }}"
            id="{{ $dropdownId }}"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Notifications">
        <i class="fas fa-bell"></i>
        @if(($communityUnreadNotificationsCount ?? 0) > 0)
            <span class="mmhc-nav-notifications-badge">{{ $communityUnreadNotificationsCount > 9 ? '9+' : $communityUnreadNotificationsCount }}</span>
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end mmhc-nav-notifications-menu shadow" aria-labelledby="{{ $dropdownId }}">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <form method="POST" action="{{ route('community.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
            </form>
        </li>
        @forelse(($communityRecentNotifications ?? collect()) as $notification)
            <li>
                <form method="POST" action="{{ route('community.notifications.open', $notification) }}">
                    @csrf
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <button type="submit" class="dropdown-item small text-start w-100 border-0 bg-transparent @if(is_null($notification->read_at)) fw-semibold @endif">
                        <strong>{{ $notification->actor->name ?? 'Member' }}</strong>
                        @if($notification->type === 'comment')
                            commented on your post
                        @elseif($notification->type === 'event_interest')
                            responded on your event
                        @else
                            reacted on your post
                        @endif
                        <div class="text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                    </button>
                </form>
            </li>
        @empty
            <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
        @endforelse
    </ul>
</div>
