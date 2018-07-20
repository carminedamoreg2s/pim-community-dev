<?php
declare(strict_types=1);

namespace Akeneo\Analytics\Component\CompletenessWidget\ReadModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelCompleteness
{
    /** @var string */
    private $channel;

    /** @var int */
    private $complete;

    /** @var int */
    private $total;

    /** @var LocaleCompleteness[] */
    private $localeCompletenesses;

    /**
     * @param string $channel
     * @param int $complete
     * @param int $total
     * @param LocaleCompleteness[] $localeCompletenesses
     */
    public function __construct(string $channel, int $complete, int $total, array $localeCompletenesses = [])
    {
        $this->channel = $channel;
        $this->complete = $complete;
        $this->total = $total;
        $this->localeCompletenesses = $localeCompletenesses;
    }

    /**
     * @return string
     */
    public function channel(): string
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function complete(): int
    {
        return $this->complete;
    }

    /**
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function localeCompletenesses(): array
    {
        return $this->localeCompletenesses;
    }

    /**
     * @param LocaleCompleteness $localeCompleteness
     */
    public function addLocalCompleteness(LocaleCompleteness $localeCompleteness): void
    {
        if (!in_array($localeCompleteness, $this->localeCompletenesses, true)) {
            $this->localeCompletenesses[$localeCompleteness->locale()] = $localeCompleteness;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $locales = [];
        foreach ($this->localeCompletenesses as $localeCompleteness){
            $locales = array_merge($locales, $localeCompleteness->toArray());
        }
        var_dump($locales);
        return [
            'total' => $this->total,
            'complete' => $this->complete,
            'locales' => $locales
        ];
    }
}
