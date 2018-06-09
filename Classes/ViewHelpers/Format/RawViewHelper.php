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
 * Outputs an argument/value without any escaping. Is normally used to output
 * an ObjectAccessor which should not be escaped, but output as-is.
 *
 * PAY SPECIAL ATTENTION TO SECURITY HERE (especially Cross Site Scripting),
 * as the output is NOT SANITIZED!
 *
 * = Examples =
 *
 * <code title="Child nodes">
 * <namespace:format.raw>{string}</namespace:format.raw>
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * <code title="Value attribute">
 * <namespace:format.raw value="{string}" />
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * <code title="Inline notation">
 * {string -> namespace:format.raw()}
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * @api
 */
class RawViewHelper extends AbstractViewHelper
{

    /**
     * @param mixed $value The value to output
     *
     * @return string
     */
    public function render($value = null)
    {
        if ($value === null) {
            return $this->renderChildren();
        } else {
            return $value;
        }
    }
}
