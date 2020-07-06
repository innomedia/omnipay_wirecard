<?php

namespace Omnipay\Wirecard\Message\Payment\Page;
use Exception;
use RuntimeException;
use SilverStripe\Dev\Debug;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Omnipay\Common\Message\AbstractResponse as OmnipayAbstractResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;

class PurchaseResponse extends OmnipayAbstractResponse implements RedirectResponseInterface
{
 /**
     * Endpoint for the hosted payment page.
     */
    protected $endpoint = '';

    /**
     * The chosen redirect method (POST by default).
     */
    protected $redirectMethod = 'GET';

    /**
     * Get the redirect endpoint, if one is set.
     */
    public function __construct(RequestInterface $request, $data,$endpoint)
    {
        $this->request = $request;
        $this->data = $data;
        $this->endpoint = $endpoint;
    }
    public function getEndpoint()
    {
        return (property_exists($this, 'endpoint') ? $this->endpoint : null);
    }

    /**
     * Not yet "successful" as user needs to be sent to Wirecard site.
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * A redirect goes to the offsite payment page.
     */
    public function isRedirect()
    {
        return true;
    }

    /**
     * The method chosen externally.
     */
    public function getRedirectMethod()
    {
        return $this->redirectMethod;
    }

    /**
     * Redirect URL will be POST.
     */
    public function getRedirectUrl()
    {
        return $this->getEndpoint();
    }

    /**
     * Data for the URL or form, including the hash.
     */
    public function getRedirectData()
    {
        return $this->getData();
    }
    protected function validateRedirect()
    {
        if (!$this instanceof RedirectResponseInterface || !$this->isRedirect()) {
            throw new RuntimeException('This response does not support redirection.');
        }

        if (empty($this->getRedirectUrl())) {
            throw new RuntimeException('The given redirectUrl cannot be empty.');
        }

        if (!in_array($this->getRedirectMethod(), ['GET', 'POST'])) {
            throw new RuntimeException('Invalid redirect method "'.$this->getRedirectMethod().'".');
        }
    }
    public function getRedirectResponse()
    {
        
        $this->validateRedirect();

        if ('GET' === $this->getRedirectMethod()) {
            return HttpRedirectResponse::create($this->getRedirectUrl());
        }
        throw new Exception("POST not");
    }
}