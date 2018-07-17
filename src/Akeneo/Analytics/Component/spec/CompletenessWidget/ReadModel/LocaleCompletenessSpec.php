<?php
declare(strict_types=1);

namespace spec\Akeneo\Analytics\Component\CompletenessWidget\ReadModel;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;

class LocaleCompletenessSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('French (Français)', 10);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleCompleteness::class);
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn(['French (Français)' => 10]);
    }
}
