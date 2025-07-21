<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Invoice;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for completed orders that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating missing invoices...');
        
        // Get completed orders without invoices
        $ordersWithoutInvoices = Order::where('status', 'completed')
            ->whereDoesntHave('invoice')
            ->get();
            
        $generated = 0;
        
        foreach ($ordersWithoutInvoices as $order) {
            $this->info("Processing order: {$order->order_number}");
            
            $invoice = $order->generateInvoice();
            
            if ($invoice) {
                $this->info("✓ Generated invoice: {$invoice->invoice_number}");
                $generated++;
            } else {
                $this->error("✗ Failed to generate invoice for order: {$order->order_number}");
            }
        }
        
        $this->info("Generated {$generated} invoices.");
        
        // Show summary
        $totalCompleted = Order::where('status', 'completed')->count();
        $totalInvoices = Invoice::count();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Completed Orders', $totalCompleted],
                ['Total Invoices', $totalInvoices],
                ['Generated This Run', $generated],
            ]
        );
        
        return 0;
    }
}
