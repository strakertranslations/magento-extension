<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;

class Attributes extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;

    /**
     * @var \Straker\EasyTranslationPlatform\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory*/
    protected $productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Straker\EasyTranslationPlatform\Helper\Data $helper,
        ProductFactory $productFactory,
        Collection $attributeCollection,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->attributeCollection = $attributeCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /* @var $model \Straker\EasyTranslationPlatform\Model\Job */
        //$model = $this->_coreRegistry->registry('st_job');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('job_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Select Attributes')]);

//        if ($model->getId()) {
//            $fieldset->addField('job_id', 'hidden', ['name' => 'job_id']);
//        }
        $fieldset->addField(
            'default_attributes',
            'multiselect',
            array(
                'name'        => 'default_attributes',
                'label'       => __('Default Attributes'),
                'title'       => __('Default Attributes'),
                'values'     => $this->getDefaultAttributes()
        ));

        $fieldset->addField(
            'custom_attributes',
            'multiselect',
            array(
                'name'        => 'custom_attributes',
                'label'       => __('Custom Attributes'),
                'title'       => __('Custom Attributes'),
                'values'     => $this->getCustomAttributes()
            ));



        //$form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Select Attributes');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Select Attributes');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function getDefaultAttributes()
    {
        $default_attributes = [];

        $DefaultTypes = ['Name','Description','Short Description','Meta Title','Meta Description','Meta Keywords'];

        $attributes = $this->productFactory->create()->getAttributes();

        foreach ($attributes as $attribute){

            if(in_array($attribute->getFrontendLabel(),$DefaultTypes)){

                $default_attributes[] = array('value'=>$attribute->getAttributeId(),'label'=> $attribute->getFrontendLabel());

                }
        }

        return $default_attributes;
    }

    public function getCustomAttributes()
    {

            $custom_attributes = [];

            $attributes = $this->attributeCollection->setEntityTypeFilter(4)
                ->addFieldToFilter('is_user_defined', array('in' => array(1)))
                ->addFieldToFilter('backend_type', array('in' => array('varchar', 'text')))
                ->setFrontendInputTypeFilter(array('in' => array('text', 'textarea','multiselect','select')));

            foreach ($attributes as $attribute){

                if($attribute->getFrontendLabel()){

                    $custom_attributes[] = array('value'=>$attribute->getAttributeId(),'label'=> $attribute->getFrontendLabel().'   ');
                }

            }

        return $custom_attributes;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
