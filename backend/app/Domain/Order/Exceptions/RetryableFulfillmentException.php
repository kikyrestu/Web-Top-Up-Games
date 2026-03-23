<?php

declare(strict_types=1);

namespace App\Domain\Order\Exceptions;

use RuntimeException;

final class RetryableFulfillmentException extends RuntimeException
{
}
