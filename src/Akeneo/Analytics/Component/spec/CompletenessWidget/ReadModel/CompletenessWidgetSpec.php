<?php
declare(strict_types=1);

namespace spec\Akeneo\Analytics\Component\CompletenessWidget\ReadModel;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\ChannelCompleteness;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let()
    {
        $localeCompletenessFr = new LocaleCompleteness('French', 2);
        $localeCompletenessEn = new LocaleCompleteness('English', 8);
        $channelCompletenessEcommerce = new ChannelCompleteness('Ecommerce', 10, 20, [$localeCompletenessFr, $localeCompletenessEn]);

        $localeCompletenessFr = new LocaleCompleteness('French', 3);
        $localeCompletenessEn = new LocaleCompleteness('English', 5);
        $channelCompletenessMobile = new ChannelCompleteness('Mobile', 5, 9, [$localeCompletenessFr, $localeCompletenessEn]);

        $this->beConstructedWith([$channelCompletenessEcommerce, $channelCompletenessMobile]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessWidget::class);
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn([
            "Ecommerce" => [
                "total" => 20,
                "complete" => 10,
                "locales" => ['French' => 2, 'English' => 8]
            ],
            "Mobile" => [
                "total" => 9,
                "complete" => 5,
                "locales" => ['French' => 3, 'English' => 5]
            ]
        ]);
    }
}
