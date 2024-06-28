<?php

namespace Avarda\PaymentWidget\Block\Product\View;

use Avarda\PaymentWidget\Helper\ConfigHelper;
use Avarda\PaymentWidget\Model\GetPaymentWidget;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class PaymentWidget extends Template
{
    protected RequestInterface $request;
    protected ProductRepositoryInterface $productRepository;
    protected ConfigHelper $configHelper;
    protected GetPaymentWidget $getPaymentWidget;

    public function __construct(
        Context $context,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        ConfigHelper $configHelper,
        GetPaymentWidget $getPaymentWidget,
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->configHelper = $configHelper;
        $this->getPaymentWidget = $getPaymentWidget;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function showWidget(): bool
    {
        return $this->configHelper->isActive();
    }

    /**
     * @return false|ProductInterface
     */
    public function getProduct()
    {
        try {
            return $this->productRepository->getById($this->getProductId());
        } catch (Exception $e) {
            return false;
        }
    }

    public function getProductId()
    {
        return $this->request->getParam('id') ?: $this->request->getParam('product_id');
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->configHelper->getLanguage();
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        $price = $this->getProduct()->getPrice();
        return $this->configHelper->getPaymentMethod($price);
    }

    /**
     * @return string|null
     */
    public function getAccountClass(): ?string
    {
        return $this->configHelper->getAccountClass($this->getPaymentMethod());
    }

    public function getWidgetUrl(): string
    {
        return $this->configHelper->getWidgetJsUrl();
    }

    public function getCustomStyles(): ?string
    {
        return $this->configHelper->getStyles();
    }

    public function getPaymentId(): string
    {
        $data = $this->getPaymentWidget->execute();
        return $data['paymentId'] ?? '';
    }

    public function getWidgetJwt(): string
    {
        $data = $this->getPaymentWidget->execute();
        return $data['widgetJwt'] ?? '';
    }
}
