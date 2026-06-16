<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Orders\ReleaseOrderTicketAction;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ReleaseExpiredOrdersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Event, 1: Ticket, 2: Order, 3: string}
     */
    private function makePendingOrder(Carbon $expiresAt): array
    {
        $event = Event::factory()->create(['sale_starts_at' => now()->subDay()]);
        $ticket = Ticket::factory()->reserved()->create(['event_id' => $event->id]);
        $order = Order::factory()->create([
            'ticket_id' => $ticket->id,
            'status' => OrderStatus::Pending,
            'expires_at' => $expiresAt,
        ]);

        $key = "available_tickets_{$event->id}";
        Redis::del($key);

        return [$event, $ticket, $order, $key];
    }

    public function test_it_releases_an_expired_pending_order_and_increments_the_counter(): void
    {
        [, $ticket, $order, $key] = $this->makePendingOrder(now()->subMinute());
        Redis::set($key, 3);

        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Expired->value]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Available->value]);
        $this->assertSame(4, (int) Redis::get($key));
    }

    public function test_it_leaves_unexpired_pending_orders_untouched(): void
    {
        [, $ticket, $order, $key] = $this->makePendingOrder(now()->addMinute());
        Redis::set($key, 3);

        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Pending->value]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Reserved->value]);
        $this->assertSame(3, (int) Redis::get($key));
    }

    public function test_it_ignores_non_pending_orders(): void
    {
        $event = Event::factory()->create(['sale_starts_at' => now()->subDay()]);
        $ticket = Ticket::factory()->sold()->create(['event_id' => $event->id]);
        $order = Order::factory()->create([
            'ticket_id' => $ticket->id,
            'status' => OrderStatus::Confirmed,
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Confirmed->value]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Sold->value]);
    }

    public function test_it_does_not_create_the_counter_when_it_is_absent(): void
    {
        [, $ticket, $order, $key] = $this->makePendingOrder(now()->subMinute());

        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Expired->value]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Available->value]);
        $this->assertSame(0, (int) Redis::exists($key));
    }

    public function test_release_is_idempotent_and_increments_the_counter_only_once(): void
    {
        [, $ticket, $order, $key] = $this->makePendingOrder(now()->subMinute());
        Redis::set($key, 3);

        $release = app(ReleaseOrderTicketAction::class);

        $this->assertTrue($release($order, OrderStatus::Expired));
        $this->assertFalse($release($order->fresh(), OrderStatus::Cancelled));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Expired->value]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => TicketStatus::Available->value]);
        $this->assertSame(4, (int) Redis::get($key));
    }
}
