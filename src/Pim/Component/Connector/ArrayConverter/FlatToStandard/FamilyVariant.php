<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariant implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(FieldsRequirementChecker $fieldChecker)
    {
        $this->fieldChecker = $fieldChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code'                => 'pc_monitors',
     *      'label-en_US'         => 'PC Monitors',
     *      'label-fr_FR'         => 'Moniteurs',
     *      'attributes'          => 'sku,name,description,price',
     *      'attribute_as_label'  => 'name',
     *      'requirements-print'  => 'sku,name,description',
     *      'requirements-mobile' => 'sku,name',
     * ]
     *
     * After:
     * [
     *      'code'                   => 'pc_monitors',
     *      'attributes'             => ['sku', 'name', 'description', 'price'],
     *      'attribute_as_label'     => 'name',
     *      'attribute_requirements' => [
     *          'mobile' => ['sku', 'name'],
     *          'print'  => ['sku', 'name', 'description'],
     *      ],
     *      'labels'                 => [
     *          'fr_FR' => 'Moniteurs',
     *          'en_US' => 'PC Monitors',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsPresence($item, ['family']);
        $this->fieldChecker->checkFieldsPresence($item, ['variant-axes_1']);
        $this->fieldChecker->checkFieldsPresence($item, ['variant-attributes_1']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['family']);
        $this->fieldChecker->checkFieldsFilling($item, ['variant-axes_1']);
        $this->fieldChecker->checkFieldsFilling($item, ['variant-attributes_1']);

        $convertedItem = ['labels' => [], 'variant_attribute_sets' => []];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, string $field, $data): array
    {
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } elseif ('' !== $data) {
            switch ($field) {
                case 'code':
                case 'family':
                    $convertedItem[$field] = (string) $data;
                    break;
                case (false !== strpos($field, 'variant-axes_')):
                    $matches = null;
                    preg_match('/^variant-axes_(?P<level>.*)$/', $field, $matches);
                    $convertedItem['variant_attribute_sets'][$matches['level'] - 1]['axes'] = explode(',', $data);
                    break;
                case (false !== strpos($field, 'variant-attributes_')):
                    $matches = null;
                    preg_match('/^variant-attributes_(?P<level>.*)$/', $field, $matches);
                    $convertedItem['variant_attribute_sets'][$matches['level'] - 1]['attributes'] = explode(
                        ',',
                        $data
                    );
                    break;
            }
        }

        return $convertedItem;
    }
}