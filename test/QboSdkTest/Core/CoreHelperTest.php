<?php

namespace QboSdkTest\Core;

use CoreHelper;
use IntuitServicesType;
use PHPUnit_Framework_TestCase;
use ServiceContext;

class SyncRestHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException IdsException
     * @expectedExceptionMessage System Failure Error: null
     */
    public function testCheckResponseErrorAndThrowException()
    {
        $requestValidatorMock = $this->getMockBuilder('OAuthRequestValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceContext = new ServiceContext(
            'CompanyID',
            IntuitServicesType::QBO,
            $requestValidatorMock
        );

        $responseError = '
            <IntuitResponse xmlns="http://schema.intuit.com/finance/v3" time="2015-04-27T16:51:42.438-07:00">
                <Fault type="SystemFault">
                    <Error code="10000">
                        <Message>An application error has occurred while processing your request</Message>
                        <Detail>System Failure Error: null</Detail>
                    </Error>
                </Fault>
            </IntuitResponse>
        ';
        CoreHelper::CheckResponseErrorAndThrowException($this->serviceContext, $responseError);
    }
}
