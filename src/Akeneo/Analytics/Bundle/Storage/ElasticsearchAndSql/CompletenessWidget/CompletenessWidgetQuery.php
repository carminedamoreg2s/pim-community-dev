<?php

declare(strict_types=1);

namespace Akeneo\Analytics\Bundle\Storage\ElasticsearchAndSql\CompletenessWidget;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\ChannelCompleteness;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;
use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\LocaleCompleteness;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidgetQuery
{
    /** @var Connection */
    private $connection;

    /** @var Client */
    private $client;

    /** @var string */
    private $indexType;

    /**
     * @param Connection $connection
     * @param Client     $client
     * @param string     $indexType
     */
    public function __construct(Connection $connection, Client $client, string $indexType)
    {
        $this->connection = $connection;
        $this->client = $client;
        $this->indexType = $indexType;
    }

    /**
     * Generate the completeness widget by searching numbers in MySQL and in Elasticsearch
     *
     * @param string $translationLocaleCode
     * @return CompletenessWidget
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget
    {
        $categoriesCodeAndLocalesByChannel = $this->getCategoriesCodesAndLocalesByChannel($translationLocaleCode);

        $totalCompleteProductsByChannel = $this->countTotalCompleteProductsInCategoriesByChannel($categoriesCodeAndLocalesByChannel);
        $totalProductsByChannel = $this->countTotalProductsInCategoriesByChannel($categoriesCodeAndLocalesByChannel);
        $localesWithNbCompleteByChannel = $this->countTotalProductInCategoriesByChannelAndLocale($categoriesCodeAndLocalesByChannel);

        return $this->generateCompletenessWidgetModel(
            $translationLocaleCode,
            $categoriesCodeAndLocalesByChannel,
            $totalProductsByChannel,
            $totalCompleteProductsByChannel,
            $localesWithNbCompleteByChannel
        );
    }

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
    public function getCategoriesCodesAndLocalesByChannel(string $translationLocaleCode): array
    {
        $sql = <<<SQL
            SELECT
                channel.code as channel_code,
                channel_translation.label as channel_label,
                JSON_ARRAY_APPEND(child.children_codes, '$', root.code) as category_codes_in_channel,
                pim_locales.json_locales as locales
            FROM
                pim_catalog_category AS root
                LEFT JOIN
                (
                    SELECT
                        child.root as root_id,
                        JSON_ARRAYAGG(child.code) as children_codes
                    FROM
                        pim_catalog_category child
                    WHERE
                        child.parent_id IS NOT NULL
                    GROUP BY
                        child.root
                ) AS child ON root.id = child.root_id
                JOIN pim_catalog_channel as channel ON root.id = channel.category_id
                LEFT JOIN pim_catalog_channel_translation as channel_translation ON channel.id = channel_translation.foreign_key
                LEFT JOIN
                (
                    SELECT
                      channel.code as channel_code,
                      channel.category_id,
                      JSON_ARRAYAGG(locale.code) as json_locales
                    FROM pim_catalog_channel as channel
                    LEFT JOIN pim_catalog_channel_locale channel_locale ON channel.id = channel_locale.channel_id
                    LEFT JOIN pim_catalog_locale locale ON channel_locale.locale_id = locale.id
                    GROUP BY
                        channel.code
                ) AS pim_locales on pim_locales.channel_code = channel.code
            WHERE
                root.parent_id IS NULL AND channel_translation.locale = :locale
            ORDER BY
                channel.code, root.code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();

        return $rows;
    }


    /**
     * Count with Elasticsearch the total number of complete products in categories, by channel
     *
     * @param array $categoriesCodeAndLocalesByChannels
     *
     * @return array
     *      ex: ['ecommerce' => 500 ]
     */
    private function countTotalCompleteProductsInCategoriesByChannel(array $categoriesCodeAndLocalesByChannels): array
    {
        if (empty($categoriesCodeAndLocalesByChannels)) {
            return null;
        }

        $body = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $locales = json_decode($categoriesCodeAndLocalesByChannel['locales']);

            $must = [];
            foreach($locales as $locale) {
                $must[] = ['term' => ["completeness." . $categoriesCodeAndLocalesByChannel['channel_code'] . ".".$locale => 100]];
            }

            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            'categories' => json_decode($categoriesCodeAndLocalesByChannel['category_codes_in_channel'])
                                        ]
                                    ],
                                    [
                                        'bool' => [
                                            'must' => $must
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $index = 0;
        $totalCompleteProductsByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $nbCompleteProducts = $rows['responses'][$index]['hits']['total'] ?? -1;
            $totalCompleteProductsByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = $nbCompleteProducts;
            $index++;
        }

        return $totalCompleteProductsByChannel;
    }

    /**
     * Count with Elasticsearch the total number of products in categories, by channel
     *
     * @param array $categoriesCodeAndLocalesByChannels
     *
     * @return array
     *      ex: ['ecommerce' => 1259 ]
     */
    private function countTotalProductsInCategoriesByChannel(array $categoriesCodeAndLocalesByChannels): array
    {
        if (empty($categoriesCodeAndLocalesByChannels)) {
            return null;
        }

        $body = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            'categories' => json_decode($categoriesCodeAndLocalesByChannel['category_codes_in_channel'])
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $index = 0;
        $totalProductsByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $nbTotalProducts = $rows['responses'][$index]['hits']['total'] ?? -1;
            $totalProductsByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = $nbTotalProducts;
            $index++;
        }

        return $totalProductsByChannel;
    }

    /**
     * Count with Elasticsearch the total number of products in categories,
     * by channel and by locale
     *
     * @param array $categoriesCodeAndLocalesByChannels
     * @return array
     *      ex: ['ecommerce' => ['fr_Fr' => 15, 'de_DE' => 1, 'en_US' => 5] ]
     */
    private function countTotalProductInCategoriesByChannelAndLocale(array $categoriesCodeAndLocalesByChannels): array
    {
        if (empty($categoriesCodeAndLocalesByChannels)) {
            return null;
        }

        $body = [];
        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $locales = json_decode($categoriesCodeAndLocalesByChannel['locales']);

            foreach($locales as $locale) {

                $body[] = [];
                $body[] = [
                    'size' => 0,
                    'query' => [
                        'constant_score' => [
                            'filter' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'terms' => [
                                                'categories' => json_decode($categoriesCodeAndLocalesByChannel['category_codes_in_channel'])
                                            ]
                                        ],
                                        [
                                            'bool' => [
                                                'should' => [
                                                    ['term' => ["completeness." . $categoriesCodeAndLocalesByChannel['channel_code'] . "." . $locale => 100]]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $index = 0;
        $localesWithNbCompleteByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $i => $categoriesCodeAndLocalesByChannel) {
            $locales = json_decode($categoriesCodeAndLocalesByChannel['locales']);
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = [];
            foreach ($locales as $locale) {
                $total = $rows['responses'][$index]['hits']['total'] ?? -1;
                $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']][$locale] = $total;
                $index++;
            }
        }

        return $localesWithNbCompleteByChannel;
    }

    /**
     * Merge all the data in a CompletenessWidget model
     *
     * @param string $translationLocaleCode
     * @param array $categoriesCodeAndLocalesByChannels
     * @param array $totalProductsByChannel
     * @param array $totalCompleteProductsByChannel
     * @param array $localesWithNbCompleteByChannel
     * @return CompletenessWidget
     */
    public function generateCompletenessWidgetModel(
            string $translationLocaleCode,
            array $categoriesCodeAndLocalesByChannels,
            array $totalProductsByChannel,
            array $totalCompleteProductsByChannel,
            array $localesWithNbCompleteByChannel
        )
    {
        $completenessWidget = new CompletenessWidget();

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $locales = json_decode($categoriesCodeAndLocalesByChannel['locales']);
            $channelCode = $categoriesCodeAndLocalesByChannel['channel_code'];
            $channelLabel = $categoriesCodeAndLocalesByChannel['channel_label'];

            $channelCompleteness = new ChannelCompleteness(
                $channelLabel,
                $totalCompleteProductsByChannel[$channelCode],
                $totalProductsByChannel[$channelCode]
            );
            foreach ($locales as $localeCode) {
                $locale = \Locale::getDisplayName($localeCode, $translationLocaleCode);
                $localeCompleteness = new LocaleCompleteness($locale, $localesWithNbCompleteByChannel[$channelCode][$localeCode]);
                $channelCompleteness->addLocalCompleteness($localeCompleteness);
            }
            $completenessWidget->addChannelCompleteness($channelCompleteness);
        }

        return $completenessWidget;
    }
}
