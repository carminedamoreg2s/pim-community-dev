<?php

namespace Akeneo\Pim\Structure\Component\AttributeType;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Image attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::IMAGE;
    }
}
