<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Akeneo\Analytics\Bundle\Storage\ElasticsearchAndSql\CompletenessWidget\GetCompletenessPerChannelAndLocale;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * Widget to display completeness of products over channels and locales
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget implements WidgetInterface
{

    /** @var UserContext */
    protected $userContext;

    /** @var GetCompletenessPerChannelAndLocale */
    protected $completenessWidgetQuery;

    /**
     * CompletenessWidget constructor.
     * @param UserContext $userContext
     * @param GetCompletenessPerChannelAndLocale $completenessWidgetQuery
     */
    public function __construct(
        UserContext $userContext,
        GetCompletenessPerChannelAndLocale $completenessWidgetQuery
    ) {
        $this->userContext      = $userContext;
        $this->completenessWidgetQuery = $completenessWidgetQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'completeness';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimDashboardBundle:Widget:completeness.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $translationLocaleCode = $this->userContext->getCurrentLocaleCode();
        $result = $this->completenessWidgetQuery->fetch($translationLocaleCode);

        return $result->toArray();
    }
}
