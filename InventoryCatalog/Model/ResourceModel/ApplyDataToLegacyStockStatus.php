<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Apply data to legacy cataloginventory_stock_status table via plain MySql query
 */
class ApplyDataToLegacyStockStatus
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param string $sku
     * @param float $quantity
     * @param int $status
     * @return void
     */
    public function execute(string $sku, float $quantity, int $status)
    {
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_status'),
            [
                StockStatusInterface::QTY => new \Zend_Db_Expr(
                    sprintf('%s + %s', StockStatusInterface::QTY, $quantity)
                ),
                StockStatusInterface::STOCK_STATUS => $status,
            ],
            [
                StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}