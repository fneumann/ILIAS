Patches, um SimpleSamlPHP für die vhb zu verwenden

Generelle SAML-Unterstützung
----------------------------

Die SAML-Unterstützung in ILIAS geht von einzelnen IdPs aus, die separat konfiguriert werden.
Deren Metadaten werden als XML im geschützesn ILIAS-Datenverzeichnis auth/saml/metadata abgelegt.
ILIAS parst daraus nur die entity-ID und legt damit einen IdP-Eintrag an, für den das Attribut-Mapping konfiguriert wird.

SimpleSamlphp ist per Composer eingebunden: libs/composer/vendor/simplesaml.php

Dort könnte es theoretisch nach Anlegen eines config-Verzeichnisses direkt konfiguriert werden,
hat dann aber keine Verbindung zu ILIAS.

ILIAS stellt seine eigenen Skripte bereit, z.B. Services/Saml/lib/saml2-acs.php
Diese lesen aus den URLs die Client-ID von ILIAS, initialisieren es und binden dann ggf. ihr Pendant von SimpleSamlPHP ein.

Ein Patch per Composer in modules/saml/lib/Auth/Source/SP.php  wird verwendet, um das obige Skript umzubiegen.

´´´´´´
-  $ar->setAssertionConsumerServiceURL(SimpleSAML\Module::getModuleURL('saml/sp/saml2-acs.php/' . $this->authId));

+  $ar->setAssertionConsumerServiceURL(ILIAS_HTTP_PATH . '/Services/Saml/lib/saml2-acs.php/default-sp/' . CLIENT_ID);
´´´´´´

Unterstützung der vhb mit Discovery Service
--------------------------------------------

(StudOnILIAS ist das Client-Verzeichnis im geschützten ILIAS-Datenverzeichnis)

* StudOnIlias/auth/saml/config/authsources.php: der IdP hat keine eigene URL, kein IdP, dafür DFN Wayf

* die Datei discoresp.php ist analog zu saml2-acs.php Im ILIAS-Service-Verzeichnis abgelegt

* StudOnIlias/auth/saml-fau/config/config.php: die metadaten-Sources sind normalerweise von ILIAS gepatcht und holen sich nur die XML-Metadaten der konfigurierten IdPs.
Hier ist nun die Gesamtliste der Ids abgelegt: StudOnIlias/auth/saml/metadata/saml20-idp-remote.php

* Das mit dem Composer-Patch eingefügte ILIAS_HTTP_PATH  falsch gesetzt und ist in diesem Branch geändert.

TODO: Attrobute der vhb aggregieren
-----------------------------------

Einbindung von https://github.com/NIIF/simplesamlphp-module-attributeaggregator