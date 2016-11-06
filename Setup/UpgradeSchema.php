<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 27/10/16
 * Time: 10:37
 */

namespace Straker\EasyTranslationPlatform\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslation;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation;

/**
 * Upgrade the CatalogRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addLabelColumn($setup);
        }

        $setup->endSetup();
    }

    /**
     * Remove Sub Product Discounts
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addLabelColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable(\Straker\EasyTranslationPlatform\Model\AttributeTranslation::ENTITY),
            'label',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Attribute Label'
            ]
        );
    }
}
