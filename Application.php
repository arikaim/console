<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

use Arikaim\Core\Console\Event\BeforeExecuteEvent;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Console\ShellCommand;

/**
 * Console application
 */
class Application
{       
    /**
     * App object
     *
     * @var Symfony\Component\Console\Application
     */
    protected $application;

    /**
     * Console app title
     *
     * @var string
     */
    protected $title;

    /**
     * App version
     *
     * @var string
     */
    protected $version;

    /**
     * Container
     *
     * @var ContainerInterface|null
     */
    protected $container = null;

    /**
     * Event dispatcher
     *
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Constructor
     *
     * @param string $title
     * @param string $version
     */
    public function __construct(string $title, string $version = '') 
    {
        $this->title = $title;
        $this->version = $version;
        $this->application = new ConsoleApplication("\n " . $title,$version);    
    
        // add shell command 
        $shell = new ShellCommand('shell',$title);
        $this->application->add($shell);
        if ($shell->isDefault() == true) {
            $this->application->setDefaultCommand($shell->getName());
        }
        // events
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addListener(BeforeExecuteEvent::EVENT_NAME, function(ConsoleCommandEvent $event) {
            // gets the command to be executed          
            $json = $event->getInput()->getOption('json');           
            $outputType = ($json == true) ? 'json' : null;         
            $event->getCommand()->setOutputType($outputType);
        });
        $this->application->setDispatcher($this->dispatcher);
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Run console cli
     *
     * @return void
     */
    public function run(): void
    {
        $this->application->run();
    }

    /**
     * Add commands to console app
     *
     * @param array $commands
     * @return void
     */
    public function addCommands(array $commands): void
    {
        foreach ($commands as $class) {          
            $command = Factory::createInstance($class);
    
            if (\is_object($command) == true) {
                $command->setDispatcher($this->dispatcher);
                $this->application->add($command);
                if ($command->isDefault() == true) {
                    $this->application->setDefaultCommand($command->getName());
                }
            }
        }     
    }
}
