<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(now());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createOrder($status = OrderStatus::Pending, $expiresAt = null)
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['sale_starts_at' => now()->subDay()]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => TicketStatus::Available,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'status' => $status,
            'expires_at' => $expiresAt ?? now()->addMinutes(5),
        ]);

        return [$user, $order];
    }

    public function test_successful_payment_of_pending_order()
    {
        [$user, $order] = $this->createOrder();

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('processPayment')->once()->andReturn(true);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Confirmed->value)
            ->assertJsonPath('data.order_id', $order->id);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Confirmed->value,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $order->ticket_id,
            'status' => TicketStatus::Sold->value,
        ]);
    }

    public function test_payment_of_already_confirmed_order()
    {
        [$user, $order] = $this->createOrder(OrderStatus::Confirmed);

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldNotReceive('processPayment');
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay", ['payment_method' => 'credit_card'])
            ->assertStatus(409);
    }

    public function test_payment_of_expired_order()
    {
        [$user, $order] = $this->createOrder(OrderStatus::Pending, now()->subMinute());

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldNotReceive('processPayment');
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay", ['payment_method' => 'credit_card'])
            ->assertStatus(410);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $order->ticket_id,
            'status' => TicketStatus::Available->value,
        ]);
    }

    public function test_failed_payment_sets_order_cancelled()
    {
        [$user, $order] = $this->createOrder();

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('processPayment')->andReturn(false);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/pay", [
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $order->ticket_id,
            'status' => TicketStatus::Available->value,
        ]);
    }

    public function test_payment_data_validation()
    {
        [$user, $order] = $this->createOrder();
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay", ['payment_method' => 'invalid_method'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_payment_data_validation_no_data()
    {
        [$user, $order] = $this->createOrder();
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_unauthenticated_user_cannot_pay()
    {
        [, $order] = $this->createOrder();

        $this->postJson("/api/orders/{$order->id}/pay", ['payment_method' => 'credit_card'])
            ->assertStatus(401);
    }

    public function test_user_cannot_pay_someone_elses_order()
    {
        [$userA, $orderA] = $this->createOrder();
        $userB = User::factory()->create();

        Sanctum::actingAs($userB);

        $this->postJson("/api/orders/{$orderA->id}/pay", ['payment_method' => 'credit_card'])
            ->assertStatus(404);
    }

    public function test_organizer_cannot_pay_an_order()
    {
        [$user, $order] = $this->createOrder();

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $this->postJson("/api/orders/{$order->id}/pay", ['payment_method' => 'credit_card'])
            ->assertStatus(404);
    }
}
