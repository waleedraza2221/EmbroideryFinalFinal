<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Invoice;

class CheckOrderInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:order-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check orders and their invoice status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Orders and Invoices...');
        $this->newLine();

        // Get all orders
        $orders = Order::with(['invoice', 'customer'])->get();
        
        if ($orders->isEmpty()) {
            $this->warn('No orders found in the database.');
            return;
        }

        $this->info("Found {$orders->count()} orders:");
        $this->newLine();

        $ordersWithoutInvoices = 0;

        foreach ($orders as $order) {
            $hasInvoice = $order->invoice ? 'YES' : 'NO';
            $invoiceId = $order->invoice ? $order->invoice->id : 'N/A';
            $invoiceStatus = $order->invoice ? $order->invoice->status : 'N/A';
            
            if (!$order->invoice) {
                $ordersWithoutInvoices++;
            }

            $this->line("Order ID: {$order->id} | Status: {$order->status} | Amount: \${$order->amount} | Has Invoice: {$hasInvoice} | Invoice ID: {$invoiceId} | Invoice Status: {$invoiceStatus}");
        }

        $this->newLine();
        
        if ($ordersWithoutInvoices > 0) {
            $this->error("Found {$ordersWithoutInvoices} orders without invoices!");
        } else {
            $this->info("All orders have invoices.");
        }

        // Show invoice statistics
        $totalInvoices = Invoice::count();
        $this->info("Total invoices in database: {$totalInvoices}");
        
        return 0;
    }
}
