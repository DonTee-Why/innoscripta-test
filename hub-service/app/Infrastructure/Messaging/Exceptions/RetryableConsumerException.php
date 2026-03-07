<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Exceptions;

use RuntimeException;

class RetryableConsumerException extends RuntimeException
{
}