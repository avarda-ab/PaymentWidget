<?php

namespace Avarda\PaymentWidget\CustomerData;

use Avarda\PaymentWidget\Model\GetPaymentWidget;
use Magento\Customer\CustomerData\SectionSourceInterface;

class PaymentWidgetJwt implements SectionSourceInterface
{
    protected GetPaymentWidget $getPaymentWidget;

    public function __construct(
        GetPaymentWidget $getPaymentWidget,
    ) {
        $this->getPaymentWidget = $getPaymentWidget;
    }

    public function getSectionData()
    {
        $data = $this->getPaymentWidget->execute();
        $data['expiredUtc'] = strtotime($data['expiredUtc'] ?? 0);
        return $data;
    }
}
