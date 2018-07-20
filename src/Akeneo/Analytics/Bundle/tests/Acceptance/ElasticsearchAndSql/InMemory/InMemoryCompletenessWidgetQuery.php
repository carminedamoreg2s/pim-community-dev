<?php

declare(strict_types=1);

namespace Akeneo\Analytics\Bundle\tests\Acceptance\ElasticsearchAndSql\InMemory;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\ChannelCompleteness;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryCompletenessWidgetQuery
{
    /** @var ArrayCollection */
    private $channelCompletenesses = [];

    public function fetch(): array
    {
        $completenessWidget = new CompletenessWidget($this->channelCompletenesses);

        return $completenessWidget->toArray();
    }

    public function addChannelCompleteness($channel, $complete, $total): void
    {
        if (!isset($this->channelCompletenesses[$channel])) {
            $this->channelCompletenesses[$channel] = new ChannelCompleteness($channel, $complete, $total);
        }
    }

    public function addLocaleCompletenessToAChannel($channel, $locale, $complete): void
    {
        $localeCompleteness = new LocaleCompleteness($locale, $complete);

        if (isset($this->channelCompletenesses[$channel])) {
            $this->channelCompletenesses[$channel]->addLocalCompleteness($localeCompleteness);
        }
    }
}
