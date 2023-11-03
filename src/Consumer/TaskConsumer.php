<?php
namespace App\Consumer;

use Kafka\Consumer;
use Kafka\ConsumerConfig;
use App\Config\KafkaConfig;
use App\Database\TaskDatabase;

class TaskConsumer {
    private $consumer;
    private $taskDatabase;

    public function __construct(TaskDatabase $taskDatabase) {
        // Kafka consumer configuration
        $config = ConsumerConfig::getInstance();
        $config->setMetadataBrokerList(KafkaConfig::getBrokers());
        $config->setGroupId('task-consumer-group');
        $config->setOffsetReset('earliest');
        $this->consumer = new Consumer();
        $this->taskDatabase = $taskDatabase;
    }

    public function consumeEvents(): void {
        $this->consumer->start(function($topic, $partition, $message) {
            $payload = json_decode($message['message']['value'], true);
            $task = $payload['task'];

            // Handle different task events
            switch ($topic) {
                case 'task_created':
                    $this->taskDatabase->createTask($task);
                    break;
                case 'task_updated':
                    $this->taskDatabase->updateTask($task['id'], $task);
                    break;
                case 'task_deleted':
                    $this->taskDatabase->deleteTask($task['id']);
                    break;
                default:
                    // Handle other topics if necessary
                    break;
            }

            return true;
        });
    }
}
