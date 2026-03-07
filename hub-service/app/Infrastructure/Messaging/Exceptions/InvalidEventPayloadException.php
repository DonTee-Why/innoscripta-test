<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Exceptions;

final class InvalidEventPayloadException extends NonRetryableConsumerException
{
}