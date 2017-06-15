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
use Magento\Framework\DB\Adapter\AdapterInterface;
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

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addLabelColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->increaseInt($setup, $context);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->addIncrement($setup);
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
        $connection->addColumn(
            $setup->getTable(\Straker\EasyTranslationPlatform\Model\AttributeTranslation::ENTITY),
            'is_published',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Is Published'
            ]
        );
        $connection->addColumn(
            $setup->getTable(\Straker\EasyTranslationPlatform\Model\AttributeTranslation::ENTITY),
            'published_at',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Published Time'
            ]
        );
        $connection->addColumn(
            $setup->getTable(\Straker\EasyTranslationPlatform\Model\AttributeTranslation::ENTITY),
            'attribute_code',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Attribute Code'
            ]
        );
    }

    private function increaseInt(SchemaSetupInterface $setup){

        $connection = $setup->getConnection();

        $foriegnKeysAttributeOptionName = $connection->getForeignKeys($setup->getTable('straker_attribute_option_translation'));

        foreach($foriegnKeysAttributeOptionName as $data){

            $connection->dropForeignKey($setup->getTable('straker_attribute_option_translation'),$data['FK_NAME']);


        }

        $connection->modifyColumn(
            $setup->getTable('straker_attribute_option_translation'),
            'attribute_translation_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => false,
            ]
        );

        $connection->modifyColumn(
            $setup->getTable('straker_attribute_translation'),
            'attribute_translation_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => false,
            ]
        );

        $connection->addForeignKey(
            $setup->getFkName($setup->getTable('straker_attribute_option_translation'), 'attribute_translation_id', $setup->getTable('straker_attribute_translation'), 'attribute_translation_id'),
            $setup->getTable('straker_attribute_option_translation'),
            'attribute_translation_id',
            $setup->getTable('straker_attribute_translation'),
            'attribute_translation_id'
        );

        $connection->addForeignKey(
            $setup->getFkName($setup->getTable('straker_attribute_option_translation'), 'option_id', 'eav_attribute_option', 'option_id'),
            $setup->getTable('straker_attribute_option_translation'),
            'option_id',
            $setup->getTable('eav_attribute_option'),
            'option_id'
        );
    }

    private function addIncrement(SchemaSetupInterface $setup)
    {

        $connection = $setup->getConnection();

        $connection->modifyColumn(
            $setup->getTable('straker_attribute_translation'),
            'attribute_translation_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'comment' => 'Attribute Translation Id',
                'primary' => true,
                'auto_increment' => true,
                'unsigned' => false,
                'nullable' => false,
            ]
        );
    }
}
