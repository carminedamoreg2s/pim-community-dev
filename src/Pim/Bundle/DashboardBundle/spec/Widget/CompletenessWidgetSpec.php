<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use Akeneo\Analytics\Bundle\Storage\ElasticsearchAndSql\CompletenessWidget\CompletenessWidgetQuery;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\ChannelCompleteness;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let(UserContext $userContext, CompletenessWidgetQuery $completenessWidgetQuery)
    {
        $this->beConstructedWith($userContext, $completenessWidgetQuery);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('completeness');
    }

    function it_exposes_the_completeness_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:completeness.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_completeness_data($completenessWidgetQuery, $userContext)
    {
        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $mobileCompleteness = new ChannelCompleteness('Mobile', 10, 40, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 10),
            'French (France)' => new LocaleCompleteness('French (France)', 0)
        ]);
        $ecommerceCompleteness = new ChannelCompleteness('E-Commerce', 25, 30, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 25),
            'French (France)' => new LocaleCompleteness('French (France)', 5)
        ]);
        $completenessWidget = new CompletenessWidget();
        $completenessWidget->addChannelCompleteness($mobileCompleteness);
        $completenessWidget->addChannelCompleteness($ecommerceCompleteness);

        $completenessWidgetQuery->fetch('en_US')->willReturn($completenessWidget);

        $this->getData()->shouldReturn(
            [
                'Mobile' => [
                    'total'    => 40,
                    'complete' => 10,
                    'locales'  => [
                        'English (United States)' => 10,
                        'French (France)'  => 0,
                    ],
                ],
                'E-Commerce' => [
                    'total' => 30,
                    'complete' => 25,
                    'locales' => [
                        'English (United States)' => 25,
                        'French (France)'  => 5,
                    ]
                ]
            ]
        );
    }
}
