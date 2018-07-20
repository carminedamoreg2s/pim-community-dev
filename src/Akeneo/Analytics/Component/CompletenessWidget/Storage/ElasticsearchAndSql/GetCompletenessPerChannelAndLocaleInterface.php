<?php

declare(strict_types=1);

namespace Akeneo\Analytics\Component\CompletenessWidget\Storage\ElasticsearchAndSql;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCompletenessPerChannelAndLocaleInterface
{
    /**
     * Generate the completeness widget by searching numbers in MySQL and in Elasticsearch
     *
     * @param string $translationLocaleCode
     * @return CompletenessWidget
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget;

    /**
     * Search, by channel, all categories children code and active locales
     *
     * @param string $translationLocaleCode
     * @return array
     *
     *          [channel_code, channel_label, [categoryCodes], [locales]]
     *
     *      ex : ['ecommerce', 'Ecommerce', ['print','cameras'...], ['de_DE','fr_FR'...]]
     *
     */
    public function getCategoriesCodesAndLocalesByChannel(string $translationLocaleCode): array;
}
