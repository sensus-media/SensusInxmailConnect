<?php declare(strict_types=1);

namespace Sensus\InxmailConnect6\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class Sync extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'sensus_inxmailconnect.sync';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}