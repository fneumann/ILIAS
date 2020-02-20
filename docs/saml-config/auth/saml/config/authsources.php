<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */
$config = array(

    // This is a authentication source which handles admin authentication.
    'admin' => array(
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ),

    // An authentication source which can authenticate against both SAML 2.0
    // and Shibboleth 1.3 IdPs.
    'default-sp' => array(
        'RelayState' => 'https://www.studon.fau.de/studon-ilias/saml.php',
        'saml:SP',
        'privatekey'  => '/nfs/iliasdata/studon/repdata/StudOnIlias/auth/saml/cert/saml.pem',
        'certificate' => '/nfs/iliasdata/studon/repdata/StudOnIlias/auth/saml/cert/saml.crt',

        // The entity ID of this SP.
        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
        //'entityID' => null,
	'entityID' => 'https://www.studon.fau.de/ilias/vhb/simplesaml/module.php/saml/sp/metadata.php/default-sp',

        // The entity ID of the IdP this should SP should contact.
        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.

        // hard code this so that no IdP disco happens
        //'idp' => 'https://www.sso.uni-erlangen.de/simplesaml/saml2/idp/metadata.php',

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => 'https://wayf.aai.dfn.de/DFN-AAI/wayf',
        //'sign.logout' => TRUE,
        //'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    ),

);
