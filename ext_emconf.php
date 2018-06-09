<?php

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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Table Cleaner',
    'description' => 'Removes [deleted/hidden] records older than [N] days from tables.',
    'category' => 'be',
    'shy' => 0,
    'version' => '2.5.0',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => 'pages',
    'clearcacheonload' => 0,
    'lockType' => '',
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@michielroos.com',
    'author_company' => 'Michiel Roos',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '4.5.0-6.2.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => ['MichielRoos\\Tablecleaner\\' => 'Classes']
    ]
];
