<?php

/**
 * Amazon S3 API Wrapper for PHP 5.3+ 
 *
 * LICENSE
 *
 * This source file is subjekt to the MIT license that is bundled
 * with this package in the file LICENSE.txt
 *
 * @category   Sirprize
 * @package    Amazon\S3
 * @copyright  Copyright (c) 2010, Christian Hoegl, Switzerland (http://sirprize.me)
 * @license    MIT License
 */


namespace Sirprize\Amazon\S3\Core;


use \Sirprize\Amazon\S3;


class EventPrinter extends S3\Core\EventListener
{

    public function __call($method, array $args)
    {
		if(!isset($args[0]) || !$args[0] instanceof S3\Core\EventArgs)
		{
			throw new S3\Exception('first argument must be an instance of Sirprize\Amazon\S3\Core\EventArgs');
		}
		
        print $args[0]->getTypeName().' // '.$args[0]->getInfo()."\n";
    }

}