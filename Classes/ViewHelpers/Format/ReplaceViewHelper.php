<?php
namespace MichielRoos\Tablecleaner\ViewHelpers\Format;

/**
 * ⓒ 2018 Michiel Roos <michiel@michielroos.com>
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Does a simple search and replace on a string
 *
 * = Examples =
 *
 * <code title="Child nodes">
 * <namespace:replace search="stringA" replace="stringB">
 *    {string}
 * </namespace:replace>
 * </code>
 * <output>
 * (Content of {string} with 'search' replaced by 'replace')
 * </output>
 *
 * <code title="Value attribute">
 * <namespace:replace search="stringA" replace="stringB" value="{string}" />
 * </code>
 * <output>
 * (Content of {string} with 'search' replaced by 'replace')
 * </output>
 *
 * @api
 */
class ReplaceViewHelper extends AbstractViewHelper
{
    /**
     * Do a simple search and replace on a string
     *
     * @param string $search
     * @param string $replace
     *
     * @return string
     */
    public function render($search = '', $replace = '')
    {
        $subject = $this->renderChildren();

        return str_replace($search, $replace, $subject);
    }
}
