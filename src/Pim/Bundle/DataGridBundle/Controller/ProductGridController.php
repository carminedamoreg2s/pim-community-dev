<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Controller;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductGridController
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FiltersConfigurator */
    private $filtersConfigurator;

    /** @var FilterExtension */
    private $filterExtension;

    /** @var int */
    private $attributesPerPage;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FiltersConfigurator          $filtersConfigurator
     * @param FilterExtension              $filterExtension
     * @param int                          $attributesPerPage
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FiltersConfigurator $filtersConfigurator,
        FilterExtension $filterExtension,
        int $attributesPerPage
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->filterExtension = $filterExtension;
        $this->attributesPerPage = $attributesPerPage;
    }

    /**
     * Get the list of attributes used as filters in the product grid.
     * For performance reasons, the number of attributes per response is limited.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAttributesAsFiltersAction(Request $request): JsonResponse
    {
        $offset = (int) $request->get('offset', 0);
        $locale = $request->get('locale', 'en_US'); // TODO: get user default locale

        $attributes = $this->attributeRepository->getAttributesUsableAsFiltersInProductGrid($locale, $this->attributesPerPage, $offset);

        // Format the list of attributes to be able to use it in FilterConfiguration and FilterExtension
        foreach ($attributes as $index => $attribute) {
            $attribute['group'] = $attribute['groupLabel'];
            $attribute['order'] = $attribute['sortOrder'];

            $attributes[$attribute['code']] = $attribute;
            unset($attributes[$index]);
        }

        $productGridConf = DatagridConfiguration::createNamed('product-grid', [
            ConfiguratorInterface::SOURCE_KEY => [
                ConfiguratorInterface::USEABLE_ATTRIBUTES_KEY => $attributes
            ],
            FilterConfiguration::FILTERS_KEY => [],
        ]);

        $this->filtersConfigurator->configure($productGridConf);

        $productGridMetadata = MetadataIterableObject::createNamed('product-grid', ['filters' => []]);
        $this->filterExtension->visitMetadata($productGridConf, $productGridMetadata);

        return new JsonResponse($productGridMetadata->offsetGet('filters'));
    }
}
