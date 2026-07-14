<?php

namespace Tests\Unit;

use App\Modules\Auth\Models\UserNotification;
use Tests\TestCase;

class UserNotificationModelTest extends TestCase
{
    public function test_notification_type_constants_are_defined(): void
    {
        $this->assertSame('booking_created', UserNotification::TYPE_BOOKING_CREATED);
        $this->assertSame('booking_cancelled', UserNotification::TYPE_BOOKING_CANCELLED);
        $this->assertSame('booking_accepted', UserNotification::TYPE_BOOKING_ACCEPTED);
        $this->assertSame('booking_rejected', UserNotification::TYPE_BOOKING_REJECTED);
        $this->assertSame('staff_assigned', UserNotification::TYPE_STAFF_ASSIGNED);
    }

    public function test_is_unread_when_read_at_null(): void
    {
        $n = new UserNotification(['read_at' => null]);
        $this->assertTrue($n->isUnread());

        $n->read_at = now();
        $this->assertFalse($n->isUnread());
    }
}
