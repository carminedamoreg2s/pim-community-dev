<?php

declare(strict_types=1);

namespace Akeneo\Analytics\Bundle\tests\Acceptance\Context;

use Akeneo\Analytics\Bundle\tests\Acceptance\ElasticsearchAndSql\InMemory\InMemoryCompletenessWidgetQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class CompletenessWidgetContext implements Context
{
    /** @var InMemoryCompletenessWidgetQuery */
    private $inMemoryCompletenessWidgetQuery;

    /** @var int */
    private $inMemoryProductTotal = 1259;

    /** @var int */
    private $inMemoryIncompleteProductTotal = 978;

    /**
     * @param InMemoryCompletenessWidgetQuery $inMemoryCompletenessWidgetQuery
     */
    public function __construct(InMemoryCompletenessWidgetQuery $inMemoryCompletenessWidgetQuery)
    {
        $this->inMemoryCompletenessWidgetQuery = $inMemoryCompletenessWidgetQuery;
    }

    /**
     * @Given the channel :channel with only complete products
     *
     * @param string $channel
     */
    public function theChannelWithOnlyCompleteProducts(string $channel): void
    {
        $this->theChannelWithOnlyCompleteProductsForLocales($channel, 'French and English');
    }

    /**
     * @Given /^the channel? (.*) with only complete products for the following locales:? (.*)$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelWithOnlyCompleteProductsForLocales(string $channel, string $locales): void
    {
        $locales = $this->listToArray($locales);
        $this->inMemoryCompletenessWidgetQuery->addChannelCompleteness($channel, $this->inMemoryProductTotal, $this->inMemoryProductTotal);

        foreach($locales as $locale){
            $this->inMemoryCompletenessWidgetQuery->addLocaleCompletenessToAChannel($channel, $locale, $this->inMemoryProductTotal);
        }
    }

    /**
     * @Given the channel :channel with some incomplete products
     *
     * @param string $channel
     */
    public function theChannelWithSomeIncompleteProducts(string $channel): void
    {
        $this->theChannelWithSomeIncompleteProductsForLocales($channel, 'French and English');
    }

    /**
     * @Given /^the channel? (.*) with some incomplete products for the following locales:? (.*)/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelWithSomeIncompleteProductsForLocales(string $channel, string $locales): void
    {
        $locales = $this->listToArray($locales);
        $this->inMemoryCompletenessWidgetQuery->addChannelCompleteness($channel, $this->inMemoryIncompleteProductTotal, $this->inMemoryProductTotal);

        foreach ($locales as $locale) {
            $this->inMemoryCompletenessWidgetQuery->addLocaleCompletenessToAChannel($channel, $locale, $this->inMemoryIncompleteProductTotal);
        }
    }

    /**
     * @When the user asks for the completeness of the catalog
     */
    public function theUserAskForTheCompletenessOfTheCatalog(): void
    {
        Assert::true(is_array($this->inMemoryCompletenessWidgetQuery->fetch()));
    }

    /**
     * @Then the report displays that the channel :channel is complete
     *
     * @param string $channel
     */
    public function theReportDisplaysThatTheChannelIsComplete(string $channel): void
    {
        $this->theReportDisplaysThatTheChannelIsCompleteForTheLocales($channel, 'French and English');
    }

    /**
     * @Then /^the report displays that the channel? (.*) is complete for the following locales:? (.*)$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theReportDisplaysThatTheChannelIsCompleteForTheLocales(string $channel, string $locales): void
    {
        $locales = $this->listToArray($locales);
        $results = $this->inMemoryCompletenessWidgetQuery->fetch();

        foreach($locales as $locale) {
            Assert::eq($results[$channel]['locales'][$locale], $results[$channel]['total']);
        }
    }

    /**
     * @Then the report displays that the channel :channel is incomplete
     *
     * @param string $channel
     */
    public function theReportDisplaysThatTheChannelIsIncomplete(string $channel): void
    {
        $this->theReportDisplaysThatTheChannelIsIncompleteForTheLocales($channel, 'French and English');
    }

    /**
     * @Then /^the report displays that the channel? (.*) is incomplete for the following locales:? (.*)$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theReportDisplaysThatTheChannelIsIncompleteForTheLocales(string $channel, string $locales): void
    {
        $locales = $this->listToArray($locales);
        $results = $this->inMemoryCompletenessWidgetQuery->fetch();

        foreach($locales as $locale) {
            Assert::notEq($results[$channel]['locales'][$locale], $results[$channel]['total']);
        }
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    public function listToArray($list)
    {
        if (empty($list)) {
            return [];
        }

        return explode(', ', str_replace(' and ', ', ', $list));
    }
}
