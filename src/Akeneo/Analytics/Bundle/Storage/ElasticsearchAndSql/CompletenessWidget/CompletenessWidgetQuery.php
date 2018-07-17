<?php

declare(strict_types=1);

namespace Akeneo\Analytics\Bundle\Storage\ElasticsearchAndSql\CompletenessWidget;

use Akeneo\Analytics\Component\CompletenessWidget\ReadModel\CompletenessWidget;
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
     * @param string $translationLocaleCode
     * @return CompletenessWidget
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget
    {
        $channelsAndLocales = $this->getLocalesByChannel($translationLocaleCode);
        $categories = $this->getChildCategoryByChannel();

        $completenessWidget = $this->countTotalProductInCategories($categories, $channelsAndLocales);

        return $completenessWidget;
    }

    /**
     * @param $translationLocaleCode
     * @return array
     */
    private function getLocalesByChannel($translationLocaleCode): array
    {
        $sql = <<<SQL
            SELECT
              channel.code as channel_code,
              ct.label as channel_label,
              JSON_ARRAYAGG(locale.code) as locale_codes
            FROM pim_catalog_channel as channel
              INNER JOIN pim_catalog_channel_locale as channel_locale ON channel.id = channel_locale.channel_id
              INNER JOIN pim_catalog_locale as locale ON channel_locale.locale_id = locale.id
              LEFT JOIN pim_catalog_channel_translation ct ON ct.foreign_key = channel.id AND ct.locale = :locale
            GROUP BY channel.code, ct.label
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();

        $locales = [];
        foreach ($rows as $row) {
            $localeCodes = null !== $row['locale_codes'] ? explode(',', $row['locale_codes']) : [];

            $locales[$row['channel_label']] = $localeCodes;
        }

        return $locales;
    }

    /**
     * @return array
     */
    private function getChildCategoryByChannel(): array
    {
        $sql = <<<SQL
            SELECT
              channel.code as channel_code,
              JSON_ARRAYAGG(child.code) as child_codes
            FROM
              pim_catalog_category as root
              INNER JOIN pim_catalog_category as child on root.id = child.parent_id
              INNER JOIN pim_catalog_channel as channel ON root.id = channel.category_id
              INNER JOIN pim_catalog_channel_locale as channel_locale ON channel.id = channel_locale.channel_id
              INNER JOIN pim_catalog_locale as locale ON channel_locale.locale_id = locale.id
            WHERE
              child.parent_id IS NOT NULL AND root.parent_id IS NULL
            GROUP BY
              channel.code, child.root
SQL;

        $rows = $this->connection->executeQuery($sql)->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $categoryCodes = null !== $row['child_codes'] ? explode(',', $row['child_codes']) : [];

            $categories[$row['channel_code']] = $categoryCodes;
        }

        return $categories;
    }

    /**
     * @param array $categoriesByChannel
     *
     * @return CompletenessWidget
     */
    private function countTotalProductInCategories(array $categoriesByChannel, array $localeByChannel): CompletenessWidget
    {
        if (empty($categoriesByChannel)) {
            return [];
        }

        $body = [];
        foreach ($categoriesByChannel as $categoryCodes) {
                $body[] = [];
                $body[] = [
                    'size' => 0,
                    'query' => [
                        'constant_score' => [
                            'filter' => [
                                'terms' => [
                                    'categories' => $categoryCodes
                                ]
                            ]
                        ]
                    ]
                ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        /** TODO - generate CompletenessWidget with ChannelCompleteness */

        $completenessWidget = new CompletenessWidget([]);

        return $completenessWidget;
    }
}
