<?php

/*
 * This file is part of Dummy Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/dummy-bundle
 */

// contao/dca/tl_module.php
$GLOBALS['TL_DCA']['tl_module']['palettes']['dummy_module'] = '{title_legend},name,headline,type;{redirect_legend},jumpTo';
$GLOBALS['TL_DCA']['tl_module']['palettes']['vue_dummy_module'] = '{title_legend},name,headline,type;{source_legend},multiSRC,imgSize,fullsize,useCaption;';
$GLOBALS['TL_DCA']['tl_module']['palettes']['vue_pixabay_module'] = '{title_legend},name,headline,type;{pixabay_legend},pixabay_api_key';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['pixabay_api_key'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['pixabay_api_key'],
	'exclude'          => true,
	'inputType'        => 'text',
	'eval'             => array('mandatory' => true, 'tl_class' => 'w50'),
	'sql'              => "varchar(128) NOT NULL default ''"
);
