<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Http;

class TelegramLogger extends AbstractProcessingHandler
{
    protected $webhookUrl;

    public function __construct($level = Logger::CRITICAL, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->webhookUrl = 'https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendMessage?chat_id='.config('services.telegram.chat_id');
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->webhookUrl) {
            return;
        }
        $message = "[" . $record->level->getName() . "] " . $record->message . "\n" . json_encode($record->context);
        Http::post($this->webhookUrl, [
            'text' => $message
        ]);
    }

    public function __invoke(array $config)
    {
        return new self(
            Logger::toMonologLevel($config['level'] ?? Logger::CRITICAL)
        );
    }
}