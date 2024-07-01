<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Contracts\MqttClient;
use PhpMqtt\Client\Facades\MQTT;

class SubscribeToTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe To MQTT topic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        /** @var MqttClient $mqtt */
        // $mqtt = MQTT::connection();
        // $mqtt->publish('myInTopic', 'foo', 1);
        // // $mqtt->subscribe('myInTopic', function (string $topic, string $message) {
        // //     echo sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message);
        // // }, 1);
        // $mqtt->loop(true);

        $mqtt = MQTT::connection();
        $mqtt->subscribe('fromMobile', function(string $topic, string $message) {
            echo sprintf('Received message on topic [%s]: %s\n',$topic, $message);
        });

        $mqtt->loop(true);
        return Command::SUCCESS;
    }
}
