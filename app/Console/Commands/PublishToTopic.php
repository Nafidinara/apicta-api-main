<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class PublishToTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish To MQTT topic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo "Publishing to topic...\n";
        $message = "Ini dari Backend!";
        MQTT::publish('fromAPI', $message);
        echo "$message\n";
        return Command::SUCCESS;
    }
}
