<?php

namespace App\Console\Commands;

use App\Models\Curator;
use Illuminate\Console\Command;

class SetCuratorPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curator:set-curator-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the Curator prices based on amount of orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $curators = Curator::all();
        foreach ($curators as $curator) {
            Curator::setCuratorPrice($curator);
        }
    }
}
