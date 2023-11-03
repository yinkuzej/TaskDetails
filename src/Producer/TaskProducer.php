<?php
namespace App\Producer;

use Kafka\Producer;
use Kafka\ProducerConfig;
use App\Config\KafkaConfig;

class TaskProducer {
    private $producer;

    public function __construct() {
        // Kafka producer configuration
        $config = ProducerConfig::getInstance();
        $config->setMetadataBrokerList(KafkaConfig::getBrokers());
        $this->producer = new Producer();
    }

    public function publishTaskEvent(string $eventType, array $task): void {
        $this->producer->send([
            [
                'topic' => $eventType,
                'value' => json_encode(['task' => $task])
            ]
        ]);
    }
}
