<?php
namespace MPHB\Payments\Gateways;
/**
 * @since 3.6.1
 */
class NexiGateway extends Gateway
{
    public function __construct()
    {
        add_filter('mphb_gateway_has_sandbox', array($this, 'hideSandbox'), 10, 2);
        parent::__construct();
    }
    protected function initId()
    {
        return 'nexi';
    }
    /**
     * @param bool $show
     * @param string $gatewayId
     * @return bool
     */
    public function hideSandbox($show, $gatewayId)
    {
        if ($gatewayId == $this->id) {
            $show = false;
        }
        return $show;
    }
    protected function setupProperties()
    {
        parent::setupProperties();
        $this->adminTitle = __('Nexi', 'motopress-hotel-booking');
    }
    protected function initDefaultOptions()
    {
        $defaults = array(
            'title'       => __('Nexi', 'motopress-hotel-booking'),
            'description' => __('Make your payment directly into Nexi.', 'motopress-hotel-booking'),
            'enabled'     => false
        );
        return array_merge(parent::initDefaultOptions(), $defaults);
    }
    public function processPayment(\MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment)
    {
        $isHolded = $this->paymentOnHold($payment);
        if ($isHolded) {
            $redirectUrl = MPHB()->settings()->pages()->getReservationReceivedPageUrl($payment);
        } else {
            $redirectUrl = MPHB()->settings()->pages()->getPaymentFailedPageUrl($payment);
        }
        wp_redirect($redirectUrl);
        exit;
    }
}
