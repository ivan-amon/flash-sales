<?php

namespace App\Console\Commands;

use App\Actions\Orders\ReleaseOrderTicketAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReleaseExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release tickets held by pending orders whose reservation window has expired.';

    /**
     * Execute the console command.
     */
    public function handle(ReleaseOrderTicketAction $releaseOrderTicket): int
    {
        $released = 0;

        Order::query()
            ->where('status', OrderStatus::Pending)
            ->where('expires_at', '<', now())
            ->chunkById(100, function (Collection $orders) use ($releaseOrderTicket, &$released): void {
                foreach ($orders as $order) {
                    if ($releaseOrderTicket($order, OrderStatus::Expired)) {
                        $released++;
                    }
                }
            });

        $this->info("Released {$released} expired order(s).");

        return self::SUCCESS;
    }
}
