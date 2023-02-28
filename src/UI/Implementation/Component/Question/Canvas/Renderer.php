<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Question\Canvas;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Question\Canvas\Inactive::class,
            Component\Question\Canvas\Active::class,
        ];
    }
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Question\Canvas\Inactive) {
            return $this->renderInactive($component, $default_renderer);
        }
        if ($component instanceof Component\Question\Canvas\Active) {
            return $this->renderActive($component, $default_renderer);
        }

        return $default_renderer->render($component);
    }


    protected function renderInactive(Component\Question\Canvas\Inactive $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.canvas_inactive.html", true, true);

        if (!empty($presentation = $component->getPresentation())) {
            if (!empty($renderer = $component->getPresentationRenderer())) {
                $tpl->setVariable('PRESENTATION', $renderer->render($presentation, $default_renderer));
            }
            else {
                // todo: provide more generic info if no specific renderer is given
                $tpl->setVariable('PRESENTATION', $presentation->getBaseSettings()->getTitle());
            }
        }
        else {
            $tpl->setVariable('PRESENTATION', 'No question presentation available');
        }

        return $tpl->get();
    }


    protected function renderActive(Component\Question\Canvas\Active $component, RendererInterface $default_renderer) : string
    {
        return '';
    }
}
