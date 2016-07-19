<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;

class Index extends \Magento\Backend\App\Action
{
    protected $_attributeCollection;
    protected $_jsonFactory;
    protected $_resultPageFactory;
    protected $_configHelper;
    protected $_xmlHelper;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        Collection $attCollection,
        ConfigHelper $configHelper,
        XmlHelper $xmlHelper
    )
    {
        $this->_attributeCollection = $attCollection;
        $this->_resultPageFactory = $pageFactory;
        $this->_jsonFactory = $jsonFactory;
        $this->_configHelper = $configHelper;
        $this->_xmlHelper = $xmlHelper;

        return parent::__construct($context);
    }

    public function execute()
    {
//        var_dump($this->_configHelper->getDefaultAttributes());
//        var_dump($this->_configHelper->getCustomAttributes());
//        var_dump($this->_configHelper->getStoreViewLanguage());

        $resultLayout = $this->_resultPageFactory->create();

        $this->_xmlHelper->create( 101 );

        $this->_xmlHelper->appendDataToRoot([
            'name' => '1_4_71_231',
            'content_context' => 'Name',
            'content_context_url' => 'http://192.168.99.100:8088/catalog/category/view/id/231',
            'content_id' => '87284',
            'value' => '<p>ghd has transformed the world of hair beauty and their irons have become an addictive accessory for women everywhere. This new and improved ghd IV Styler make it better than ever for straightening and smoothing hair or creating beautiful body, waves and curls.</p>

<p>The new sleek design has a more rounded barrel which makes it easy to curl and wave your hair, while the ceramic plates have even better temperature regulation, giving stunning straightness, smoothness and shine.</p>

<p>The ghd hair straightener has a &quot;sleep mode&quot; so it automatically turns itself off after 30 minutes. They have a &quot;shiver&quot; function that prevents damage to your irons on those cold winter mornings, and because it is digital and adapts to international voltages, you can experience spectacular salon style wherever you are in the world, from Oxford Street to 5th Avenue!</p>

<p><strong>ghd IV Styler - Technical Features: </strong></p>

<p><strong>Advance Ceramic Heaters</strong><br />
The ghd IV Styler comes with the ultimate surface for a static free sheen.</p>

<p><strong>New Rounder Barrel</strong><br />
For the perfect curls, waves or flicks, as well as a super straight look, the rounder barrel of ghd IV Styler gives you ultimate control. It&rsquo;s so easy to create waves, curls and flicks that you&#39;ll never need the same look twice.</p>

<p><strong>New Sleep Mode</strong><br />
A new sleep mode safely turns the heaters off on your ghd IV Styler when left unattended for 30 minutes. So don&rsquo;t worry if you do forget, your house will still be in one piece when you get home.</p>

<p><strong>Now with Universal Voltage</strong><br />
With universal voltage you can get optimum performance from your ghd IV Styler anywhere in the world.</p>

<p><strong>Unique Digital Technology</strong><br />
Improved temperature control for even better styling, feel more confident with your ghd IV Styler.</p>

<p><strong>Shiver Mode</strong><br />
Internal condensation can damage stylers. So when the room temperature is below 8&deg;C, your ghd IV styler will shut down to protect itself until the temperature rises again.</p>

<p><strong>Hologram</strong><br />
You can be confident you have purchased a genuine ghd product by this hologram sticker.</p>

<p><strong>On/Off Button</strong><br />
The ghd IV Styler conveniently allows you to turn your styler off without unplugging.</p>

<p><strong>Swivel Cord Attachment</strong><br />
The swivel cord attachment of the ghd IV Styler helps stop the cord from becoming twisted.</p>

<p><strong>Floating Plates</strong><br />
Floating plates in ghd IV Styler ensure even pressure distribution.</p>

<p><strong>UK three pin plug only</strong></p>'
        ]);

        $this->_xmlHelper->appendDataToRoot([
            'name' => '1_4_71_232',
            'content_context' => 'Description',
            'content_context_url' => 'http://192.168.99.100:8088/catalog/category/view/id/232',
            'content_id' => '87285',
            'value' => 'Novel by Charles Dickens, published both serially and in book form in 1859. The story is set in the late 18th century against the background of the French Revolution.While political events drive the story, Dickens takes a decidedly antipolitical tone, lambasting both aristocratic tyranny and revolutionary excess--the latter memorably caricatured in Madame Defarge, who knits beside the guillotine. The book is perhaps best known for its opening lines, "It was the best of times, it was the worst of times," and for Carton\'s last speech, in which he says of his replacing Darnay in a prison cell, "It is a far, far better thing that I do, than I have ever done; it is a far, far better rest that I go to, than I have ever known.'
        ]);

        $this->_xmlHelper->saveXmlFile();

        exit();

        return $resultLayout;
    }

}
