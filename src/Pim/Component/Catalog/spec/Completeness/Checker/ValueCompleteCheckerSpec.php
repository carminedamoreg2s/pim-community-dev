<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;

class ValueCompleteCheckerSpec extends ObjectBehavior
{
    function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface');
    }

    function it_tells_the_value_is_not_complete_by_default(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    function it_supports_non_localisable_and_non_scopable_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_localisable_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_locale_specific_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->hasLocaleSpecific($locale)->willReturn(true);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_scopable_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channel);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_scopable_and_localisable_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channel);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_does_not_supports_a_locale_that_does_not_match_the_localisable_value(
        ValueInterface $value,
        LocaleInterface $localeValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($localeValue);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_does_not_supports_a_locale_that_does_not_match_the_locale_specific_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->hasLocaleSpecific($locale)->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_does_not_supports_a_channel_that_does_not_match_the_channel_value(
        ValueInterface $value,
        ChannelInterface $channelValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channelValue);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_successfully_checks_incomplete_attribute(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $completenessChecker->supportsValue($value, $channel, $locale)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(false);

        $this->addProductValueChecker($completenessChecker);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    function it_successfully_checks_complete_attribute(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->addProductValueChecker($completenessChecker);

        $completenessChecker->supportsValue($value, $channel, $locale)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(true);

        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
