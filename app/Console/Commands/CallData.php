<?php

namespace App\Console\Commands;

use App\Models\Temp_files;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CallData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minute:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        return DB::table('temp_files')->delete();
    }
}
