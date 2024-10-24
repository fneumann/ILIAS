<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

declare(strict_types=1);

/**
 * Class ilOrgUnitAuthorityInputGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAuthorityInputGUI extends ilFormPropertyGUI implements ilMultiValuesItem // TODO: still in use?
{
    /**
     * @var ilOrgUnitAuthority[]
     */
    protected $value;
    private ilOrgUnitPositionDBRepository $positionRepo;

    /**
     * ilOrgUnitAuthorityInputGUI constructor.
     * @param string $a_title
     * @param string $a_postvar
     */
    public function __construct($a_title, $a_postvar)
    {
        parent::__construct($a_title, $a_postvar);
    }

    private function getPositionRepo(): ilOrgUnitPositionDBRepository
    {
        if (!isset($this->positionRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            $this->positionRepo = $dic["repo.Positions"];
        }

        return $this->positionRepo;
    }

    /**
     * @param \ilTemplate $a_tpl
     */
    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * @param array $values
     */
    public function setValueByArray(array $values): void
    {
        $authorities = $values[$this->getPostVar()];
        if (!is_array($authorities)) {
            $authorities = [];
        }
        foreach ($authorities as $authority) {
            assert($authority instanceof ilOrgUnitAuthority);
        }
        $this->setValue($authorities);
    }

    /**
     * @param \ilOrgUnitAuthority[] $a_value
     */
    public function setValue(array $a_value): void
    {
        $this->value = $a_value;
    }

    /**
     * @return \ilOrgUnitAuthority[]
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @throws ilTemplateException
     */
    protected function render(): string
    {
        $tpl = new ilTemplate("tpl.authority_input.html", true, true, "components/ILIAS/OrgUnit");
        //		if (strlen($this->getValue())) {
        //			$tpl->setCurrentBlock("prop_text_propval");
        //			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        //			$tpl->parseCurrentBlock();
        //		}

        //$tpl->setVariable("POSITION_ID", $this->getFieldId());

        $postvar = $this->getPostVar();
        //		if ($this->getMulti() && substr($postvar, - 2) != "[]") {
        //			$postvar .= "[]";
        //		}

        $tpl->setVariable("POST_VAR", $postvar);

        // SCOPE
        $scope_html = "";
        foreach (ilOrgUnitAuthority::getScopes() as $scope) {
            $txt = $this->dic()->language()->txt('scope_' . $scope);
            $scope_html .= "<option value='{$scope}'>{$txt}</option>";
        }
        $tpl->setVariable("SCOPE_OPTIONS", $scope_html);

        // Over
        $over_everyone = ilOrgUnitAuthority::OVER_EVERYONE;
        $title = $this->lang()->txt('over_' . $over_everyone);
        $over_html = "<option value='{$over_everyone}'>{$title}</option>";
        foreach ($this->getPositionRepo()->getArray('id', 'title') as $id => $title) {
            $over_html .= "<option value='{$id}'>{$title}</option>";
        }
        $tpl->setVariable("OVER_OPTIONS", $over_html);
        /**
         * @var $ilOrgUnitAuthority ilOrgUnitAuthority
         */
        if ($this->getMultiValues()) {
            foreach ($this->getMultiValues() as $ilOrgUnitAuthority) {
                //				$tpl->setVariable("OVER_OPTIONS", $over_html);  // TODO: remove?
            }
        }

        if ($this->getRequired()) {
            //			$tpl->setVariable("REQUIRED", "required=\"required\""); // TODO: remove?
        }

        $tpl->touchBlock("inline_in_bl");
        $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        $this->initJS();

        return $tpl->get();
    }

    protected function dic(): \ILIAS\DI\Container
    {
        return $GLOBALS["DIC"];
    }

    protected function lang(): \ilLanguage
    {
        static $loaded;
        $lang = $this->dic()->language();
        if (!$loaded) {
            $lang->loadLanguageModule('orgu');
            $loaded = true;
        }

        return $lang;
    }

    public function getMulti(): bool
    {
        return false;
    }

    protected function initJS(): void
    {
        // Global JS
        /**
         * @var $globalTpl \ilTemplate
         */
        $globalTpl = $GLOBALS['DIC'] ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
        $globalTpl->addJavascript("assets/js/authority.js");

        $config = json_encode(array());

        $authorities = $this->getValue();
        $auth = [];
        foreach ($authorities as $authority) {
            $auth[] = [
                'id' => $authority->getId(),
                'over' => $authority->getOver(),
                'scope' => $authority->getScope()
            ];
        }
        $data = json_encode($auth);

        $globalTpl->addOnLoadCode("ilOrgUnitAuthorityInput.init({$config}, {$data});");
    }
}
