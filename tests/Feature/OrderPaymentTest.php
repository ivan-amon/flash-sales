<?php

declare(strict_types=1);

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

    public function test_create_payment_intent_returns_client_secret()
    {
        [$user, $order] = $this->createOrder();

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('createPaymentIntent')->once()->andReturn([
                'id' => 'pi_test_123',
                'client_secret' => 'pi_test_123_secret_abc',
            ]);
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/payment-intent")
            ->assertStatus(200)
            ->assertJsonPath('client_secret', 'pi_test_123_secret_abc');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
        ]);
    }

    public function test_cannot_create_payment_intent_for_non_pending_order()
    {
        [$user, $order] = $this->createOrder(OrderStatus::Confirmed);

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldNotReceive('createPaymentIntent');
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/payment-intent")
            ->assertStatus(409);
    }

    public function test_successful_payment_of_pending_order()
    {
        [$user, $order] = $this->createOrder();

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('verifyPayment')->once()->andReturn(true);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/pay");

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
            $mock->shouldNotReceive('verifyPayment');
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay")
            ->assertStatus(409);
    }

    public function test_payment_of_expired_order()
    {
        [$user, $order] = $this->createOrder(OrderStatus::Pending, now()->subMinute());

        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldNotReceive('verifyPayment');
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/pay")
            ->assertStatus(410);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Expired->value,
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
            $mock->shouldReceive('verifyPayment')->andReturn(false);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/pay");

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

    public function test_unauthenticated_user_cannot_pay()
    {
        [, $order] = $this->createOrder();

        $this->postJson("/api/orders/{$order->id}/pay")
            ->assertStatus(401);
    }

    public function test_user_cannot_pay_someone_elses_order()
    {
        [$userA, $orderA] = $this->createOrder();
        $userB = User::factory()->create();

        Sanctum::actingAs($userB);

        $this->postJson("/api/orders/{$orderA->id}/pay")
            ->assertStatus(404);
    }

    public function test_organizer_cannot_pay_an_order()
    {
        [$user, $order] = $this->createOrder();

        $organizer = Organizer::factory()->create();
        Sanctum::actingAs($organizer);

        $this->postJson("/api/orders/{$order->id}/pay")
            ->assertStatus(404);
    }
}
