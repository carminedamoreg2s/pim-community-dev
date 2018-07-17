<?php
declare(strict_types=1);

namespace spec\Akeneo\Analytics\Component\CompletenessWidget\ReadModel;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\ChannelCompleteness;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;

class ChannelCompletenessSpec extends ObjectBehavior
{
    function let()
    {
        $localeCompletenessFr = new LocaleCompleteness('French', 2);
        $localeCompletenessEn = new LocaleCompleteness('English', 8);
        $this->beConstructedWith('Ecommerce', 10, 20, [$localeCompletenessFr, $localeCompletenessEn]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelCompleteness::class);
    }

    function it_can_return_channel()
    {
        $this->channel()->shouldReturn('Ecommerce');
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn([
            "total" => 20,
            "complete" => 10,
            "locales" => ['French' => 2, 'English' => 8]
        ]);
    }
}
