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
 * Before comment execute console event
 */
class BeforeExecuteEvent extends ConsoleEvent 
{       
    const EVENT_NAME = 'before.execute.commmand';    
}
