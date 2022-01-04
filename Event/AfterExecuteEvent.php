<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Console\Event;

use Symfony\Component\Console\Event\ConsoleEvent;

/**
 * After comment execute console event
 */
class AfterExecuteEvent extends ConsoleEvent 
{       
    const EVENT_NAME = 'after.execute.commmand';
}
