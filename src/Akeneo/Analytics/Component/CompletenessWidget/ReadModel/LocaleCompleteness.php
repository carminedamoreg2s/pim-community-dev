<?php
declare(strict_types=1);

namespace Akeneo\Analytics\Component\CompletenessWidget\ReadModel;

/**
 *
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleCompleteness
{
    /** @var string */
    private $locale;

    /** @var int */
    private $complete;

    /**
     * @param string $locale
     * @param int $complete
     */
    public function __construct(string $locale, int $complete)
    {
        $this->locale = $locale;
        $this->complete = $complete;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function complete(): int
    {
        return $this->complete;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
          $this->locale => $this->complete
        ];
    }
}
