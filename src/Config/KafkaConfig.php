<?php
namespace App\Config;

class KafkaConfig {
    // Kafka broker configuration, we specify the brokers here

    public static function getBrokers(): string {
        return 'localhost:9092';
    }
}
