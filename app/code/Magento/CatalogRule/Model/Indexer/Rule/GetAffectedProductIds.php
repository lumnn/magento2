<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;

class GetAffectedProductIds
{
    /**
     * @param CollectionFactory $ruleCollectionFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        private readonly CollectionFactory $ruleCollectionFactory,
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Get affected product ids by rule ids
     *
     * @param array $ids
     * @return array
     */
    public function execute(array $ids): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['t' => $this->resource->getTableName('catalogrule_product')],
                ['t.product_id']
            )
            ->where(
                't.rule_id IN (?)',
                array_map('intval', $ids)
            )
            ->distinct(
                true
            );
        $productIds = array_map('intval', $connection->fetchCol($select));
        $rules = $this->ruleCollectionFactory->create()
            ->addFieldToFilter('rule_id', ['in' => array_map('intval', $ids)]);
        foreach ($rules as $rule) {
            /** @var Rule $rule */
            array_push($productIds, ...array_keys($rule->getMatchingProductIds()));
        }
        return array_values(array_unique($productIds));
    }
}
