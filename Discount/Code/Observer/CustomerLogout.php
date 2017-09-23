<?php
/**
 * Created by PhpStorm.
 * User: FMbugua
 * Date: 9/23/2017
 * Time: 3:13 PM
 */
namespace Discount\Code\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
class CustomerLogout implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;
    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->session = $customerSession;
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;
        $this->responseFactory = $responseFactory;
    }
    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }
        return $this->cookieMetadataManager;
    }
    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }
        return $this->cookieMetadataFactory;
    }
    /**
     * Handler for 'checkout_onepage_controller_success_action' event.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $lastCustomerId = $this->session->getId();
        $this->session->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
        // redirect to the homepage after logout
        $resultRedirect = $this->responseFactory->create();
        $resultRedirect->setRedirect($this->urlBuilder->getUrl('/'))->sendResponse('200');
        exit();
    }
}