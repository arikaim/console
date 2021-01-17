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

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Before comment execute console event
 */
class BeforeExecuteEvent extends ConsoleCommandEvent 
{       
    const EVENT_NAME = 'before.execute.commmand';

    /**
     * Constructor
     *
     * @param Command|null $command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(Command $command = null, InputInterface $input, OutputInterface $output)
    {
        parent::__construct($command,$input,$output);
    }
}
