<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Akeneo\Analytics\Bundle\Storage\ElasticsearchAndSql\CompletenessWidget\CompletenessWidgetQuery;
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

    /** @var CompletenessWidgetQuery */
    protected $completenessWidgetQuery;

    /**
     * CompletenessWidget constructor.
     * @param UserContext $userContext
     * @param CompletenessWidgetQuery $completenessWidgetQuery
     */
    public function __construct(
        UserContext $userContext,
        CompletenessWidgetQuery $completenessWidgetQuery
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
        /*$userLocale = $this->userContext->getCurrentLocaleCode();
        $channels = $this->completenessRepo->getProductsCountPerChannels($userLocale);
        $completeProducts = $this->completenessRepo->getCompleteProductsCountPerChannels($userLocale);

        $data = [];
        foreach ($channels as $channel) {
            $data[$channel['label']] = [
                'total'    => (int) $channel['total'],
                'complete' => 0,
            ];
        }
        foreach ($completeProducts as $completeProduct) {
            $locale = $this->localeRepository->findOneByIdentifier($completeProduct['locale']);
            if (!$this->objectFilter->filterObject($locale, 'pim.internal_api.locale.view')) {
                $localeLabel = $this->getCurrentLocaleLabel($completeProduct['locale']);
                $data[$completeProduct['label']]['locales'][$localeLabel] = (int) $completeProduct['total'];
                $data[$completeProduct['label']]['complete'] += $completeProduct['total'];
            }
        }

        $data = array_filter($data, function ($channel) {
            return isset($channel['locales']);
        });

        return $data;*/

        $translationLocale = $this->userContext->getCurrentLocaleCode();

        $result = $this->completenessWidgetQuery->fetch($translationLocale);
        return $result->toArray();
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    private function getCurrentLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->userContext->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
    }
}
