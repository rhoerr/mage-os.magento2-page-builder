<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageBuilder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\PageBuilder\Setup\MigrateImagesToPageBuilder;

class MigrateToPageBuilder implements DataPatchInterface
{
    /**
     * @var \Magento\PageBuilder\Setup\ConvertBlueFootToPageBuilderFactory
     */
    private $convertBlueFootToPageBuilderFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var MigrateImagesToPageBuilder $imageMigration
     */
    private $imageMigration;

    /**
     * Constructor
     *
     * @param \Magento\PageBuilder\Setup\ConvertBlueFootToPageBuilderFactory $convertBlueFootToPageBuilderFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param MigrateImagesToPageBuilder $imageMigration
     */
    public function __construct(
        \Magento\PageBuilder\Setup\ConvertBlueFootToPageBuilderFactory $convertBlueFootToPageBuilderFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        MigrateImagesToPageBuilder $imageMigration
    ) {
        $this->convertBlueFootToPageBuilderFactory = $convertBlueFootToPageBuilderFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->imageMigration = $imageMigration;
    }

    /**
     * Apply data conversion
     *
     * @return void
     */
    public function apply()
    {
        if ($this->moduleDataSetup->tableExists('gene_bluefoot_entity')) {
            $this->updateEavConfiguration();
            $this->convertBlueFootToPageBuilderFactory->create(['setup' => $this->moduleDataSetup])->convert();
            $this->imageMigration->migrate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Update EAV configuration for entity and attributes
     */
    private function updateEavConfiguration()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->update(
            $this->moduleDataSetup->getTable('eav_entity_type'),
            [
                'entity_model' => \Magento\PageBuilder\Model\ResourceModel\Entity::class,
                'attribute_model' => \Magento\PageBuilder\Model\Attribute::class,
                'entity_attribute_collection' => \Magento\PageBuilder\Model\ResourceModel\Attribute\Collection::class
            ],
            $connection->quoteInto('entity_type_code = ?', 'gene_bluefoot_entity')
        );

        $entityTypeIdSelect = $connection->select()
            ->from($this->moduleDataSetup->getTable('eav_entity_type'), ['entity_type_id'])
            ->where('entity_type_code = ?', 'gene_bluefoot_entity');
        $entityTypeId = $connection->fetchOne($entityTypeIdSelect);

        $attributeIdsSelect = $connection->select()
            ->from($this->moduleDataSetup->getTable('eav_attribute'), ['attribute_id'])
            ->where(
                'attribute_code IN (?)',
                [
                    'block_id',
                    'category_id',
                    'product_id',
                    'map',
                    'video_url'
                ]
            )
            ->where('entity_type_id = ?', $entityTypeId);
        $connection->update(
            $this->moduleDataSetup->getTable('gene_bluefoot_eav_attribute'),
            [
                'data_model' => new \Zend_Db_Expr('NULL')
            ],
            $connection->quoteInto('attribute_id IN (?)', $connection->fetchCol($attributeIdsSelect))
        );

        $attributeIdsSelect = $connection->select()
            ->from($this->moduleDataSetup->getTable('eav_attribute'), ['attribute_id'])
            ->where(
                'attribute_code IN (?)',
                [
                    'advanced_slider_items',
                    'button_items',
                    'slider_items',
                    'accordion_items',
                    'tabs_items'
                ]
            )
            ->where('entity_type_id = ?', $entityTypeId);
        $connection->update(
            $this->moduleDataSetup->getTable('eav_attribute'),
            [
                'source_model' => new \Zend_Db_Expr('NULL')
            ],
            $connection->quoteInto('attribute_id IN (?)', $connection->fetchCol($attributeIdsSelect))
        );
    }
}
