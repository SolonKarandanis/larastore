<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PayoutVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform vendors payout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Log the start of the process
        $this->info('Starting monthly payout process for vendors...');
        $vendors = Vendor::eligibleForPayout()->get();
        foreach ($vendors as $vendor) {
            $this->processPayout($vendor);
        }

        $this->info('Finished monthly payout process for vendors...');
        return Command::SUCCESS;
    }

    protected function processPayout(Vendor $vendor)
    {
        $this->info('Processing payout for vendor [ID=' .$vendor->user_id. ']');
        try{
            DB::beginTransaction();
            $startingFrom = Payout::where('vendor_id',$vendor->user_id)
                ->orderBy('until','desc')
                ->value('until');
        }
        catch (\Exception $exception){

        }
    }
}
