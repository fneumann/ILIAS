<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderDescriptionTest extends ilCertificateBaseTestCase
{
    public function testCreateHtmlDescription()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt', 'loadLanguageModule'))
            ->getMock();

        $templateMock = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->method('get')
            ->willReturn('');

        $userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn(array());

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilDefaultPlaceholderDescription($languageMock, $userDefinePlaceholderMock);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescription()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt', 'loadLanguageModule'))
            ->getMock();

        $languageMock->expects($this->exactly(16))
            ->method('txt')
            ->willReturn('Something translated');

        $userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn(array());

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilDefaultPlaceholderDescription($languageMock, $userDefinePlaceholderMock);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
                'USER_LOGIN'         => 'Something translated',
                'USER_FULLNAME'      => 'Something translated',
                'USER_FIRSTNAME'     => 'Something translated',
                'USER_LASTNAME'      => 'Something translated',
                'USER_TITLE'         => 'Something translated',
                'USER_SALUTATION'    => 'Something translated',
                'USER_BIRTHDAY'      => 'Something translated',
                'USER_INSTITUTION'   => 'Something translated',
                'USER_DEPARTMENT'    => 'Something translated',
                'USER_STREET'        => 'Something translated',
                'USER_CITY'          => 'Something translated',
                'USER_ZIPCODE'       => 'Something translated',
                'USER_COUNTRY'       => 'Something translated',
                'USER_MATRICULATION' => 'Something translated',
                'DATE'               => 'Something translated',
                'DATETIME'           => 'Something translated'
            ),
            $placeHolders
        );
    }
}
