<?php

namespace Straker\EasyTranslationPlatform\Plugin\Form;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\View\Factory;
use Closure;


class Plugin
{
    public function __construct(
        ConfigHelper $configHelper,
        UrlInterface $url
    ) {
        $this->_configHelper = $configHelper;
        $this->_url = $url;
    }

    public function aroundtoHtml(
        Fieldset $subject,
        Closure $proceed
    )
    {
        $subject->_template = 'Straker_EasyTranslationPlatform::language/form.phtm';

        return $proceed;
    }
}