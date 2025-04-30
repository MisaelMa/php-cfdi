<?php

namespace Sat\Csd;

use Exception;
use DateTime;
use Throwable;

class ExeptionCSD extends Exception
{
  /**
   * @var string
   */
  private $foo;

  /**
   * @var DateTime
   */
  private $date;

  /**
   * Constructor
   *
   * @param string $foo
   * @param string $message
   * @param int $code
   * @param Throwable|null $previous
   */
  public function __construct(
    string $foo = 'bar',
    string $message = "",
    int $code = 0,
    ?Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);

    $this->foo = $foo;
    $this->date = new DateTime();
  }

  /**
   * Get foo value
   *
   * @return string
   */
  public function getFoo(): string
  {
    return $this->foo;
  }

  /**
   * Get date value
   *
   * @return DateTime
   */
  public function getDate(): DateTime
  {
    return $this->date;
  }
}
