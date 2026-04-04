<?php

namespace App\Console\Commands;

use App\Services\BalanceService;
use Illuminate\Console\Command;

class ReleaseBalanceCommand extends Command
{
    protected $signature   = 'balance:release';
    protected $description = 'Release held balances that have passed the holding period';

    public function __construct(private BalanceService $balanceService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Releasing held balances...');
        $this->balanceService->releaseHeldBalances();
        $this->info('Done.');
    }
}
