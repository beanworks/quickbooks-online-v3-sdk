<?php

namespace QboSdkTest\Core\RestCalls;

use CoreConstants;
use IntuitServicesType;
use IPPBill;
use OAuthException;
use PHPUnit_Framework_TestCase;
use RequestParameters;
use ServiceContext;
use SyncRestHandler;
use XmlObjectSerializer;

class SyncRestHandlerTest extends PHPUnit_Framework_TestCase
{
    private $oauthMock;
    private $serviceContext;
    private $syncRestHandler;

    public function setUp()
    {
        parent::setUp();

        $this->oauthMock = $this->getMockBuilder('OAuth')
            ->disableOriginalConstructor()
            ->setMethods(array('fetch', 'getLastResponse', 'getLastResponseHeaders'))
            ->getMock();

        $requestValidatorMock = $this->getMockBuilder('OAuthRequestValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceContext = new ServiceContext(
            'CompanyID',
            IntuitServicesType::QBO,
            $requestValidatorMock
        );

        $this->syncRestHandler = new SyncRestHandler($this->serviceContext);
        $this->syncRestHandler->setOauth($this->oauthMock);
    }

    public function testGetResponseSuccessWithValidReturn()
    {
        $this->oauthMock->method('getLastResponse')
            ->willReturn('Success');

        list(
            $requestParameters,
            $httpsPostBody
        ) = $this->getReqParamsAndPostBody();
        list(
            $responseCode,
            $responseBody,
            $responseError
        ) = $this->syncRestHandler->GetResponse($requestParameters, $httpsPostBody, null);

        $this->assertEquals('Success', $responseBody);
        $this->assertNull($responseError);
    }

    public function testGetResponseFailureFaultDetail()
    {
        $this->oauthMock->method('fetch')
            ->will($this->returnCallback(function() {
                throw new OAuthException('Error');
            }));
        $this->oauthMock->method('getLastResponse')
            ->willReturn('Failure');

        list(
            $requestParameters,
            $httpsPostBody
        ) = $this->getReqParamsAndPostBody();
        list(
            $responseCode,
            $responseBody,
            $responseError
        ) = $this->syncRestHandler->GetResponse($requestParameters, $httpsPostBody, null);

        $this->assertNull($responseBody);
        $this->assertEquals('Failure', $responseError);
    }

    private function getReqParamsAndPostBody()
    {
        $entity = new IPPBill();
        $httpsPostBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($entity, $urlResource);
        $uri = implode(
            CoreConstants::SLASH_CHAR,
            array('company', $this->serviceContext->realmId, $urlResource.'?operation=update')
        );
        $requestParameters = new RequestParameters($uri, 'POST', CoreConstants::CONTENTTYPE_APPLICATIONXML, null);

        return array($requestParameters, $httpsPostBody);
    }
}
