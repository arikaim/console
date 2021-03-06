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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;

use Arikaim\Core\Console\ConsoleHelper;
use Arikaim\Core\Console\Event\BeforeExecuteEvent;
use Arikaim\Core\Console\Event\AfterExecuteEvent;

/**
 * Base class for all commands
 */
abstract class ConsoleCommand extends Command
{       
    /**
     * Style obj reference
     *
     * @var SymfonyStyle
     */
    protected $style;

    /**
     * Set to true for default command
     *
     * @var bool
     */
    protected $default = false;
    
    /**
     * Table output
     *
     * @var Symfony\Component\Console\Helper\Table
     */
    protected $table = null;

    /**
     * Event dispatcher
     *
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Output type
     *
     * @var string|null
     */
    protected $outputType = null;

    /**
     * Command result data
     *
     * @var array
     */
    protected $result = [];

    /**
     * Constructor
     *
     * @param string|null $name
     * @param string|null $description
     */
    public function __construct(?string $name = null, ?string $description = null) 
    {
        parent::__construct($name);
    
        $this->default = false;

        if (empty($name) == false) {
            $this->setName($name);
        }
        if (empty($description) == false) {
            $this->setDescription($description);
        }
    }

    /**
     * Set output type
     *
     * @param string|null $outputType
     * @return void
     */
    public function setOutputType(?string $outputType): void
    {
        $this->outputType = $outputType;
    }

    /**
     * Get execution result
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Return true if output is to console
     *
     * @return boolean
     */
    public function isConsoleOutput()
    {
        return empty($this->outputType);
    }

    /**
     * Set event dispatcher
     *
     * @param object $dispatcher
     * @return void
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Abstract method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    abstract protected function executeCommand($input, $output);

    /**
     * Run method wrapper
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input,$output);
        $this->table = new Table($output);
        $this->addOptionalOption('output','Output format',false);
        $beforeEvent = new BeforeExecuteEvent($this,$input,$output);      
        $this->dispatcher->dispatch(BeforeExecuteEvent::EVENT_NAME,$beforeEvent);

        $exitCode = parent::run($input, $output);
        $this->result['status'] = ($exitCode == 0) ? 'ok' : 'error';
        
        // command executed
        $afterEvent = new AfterExecuteEvent($this,$input,$output);
        $this->dispatcher->dispatch(AfterExecuteEvent::EVENT_NAME,$afterEvent);

        return $exitCode;
    }
 
    /**
     * Add required argument 
     *
     * @param string $name
     * @param string $description
     * @param mixed|null $default
     * @return void
     */
    public function addRequiredArgument(string $name, string $description = '', $default = null): void
    {
        $this->addArgument($name,InputArgument::REQUIRED,$description,$default);
    }

    /**
     * Add optional argument
     *
     * @param string $name
     * @param string $description
     * @param mixed|null $default
     * @return void
     */
    public function addOptionalArgument(string $name, string $description = '', $default = null): void
    {
        $this->addArgument($name,InputArgument::OPTIONAL,$description,$default);
    }

    /**
     * Add optional option
     *
     * @param string $name
     * @param string $description
     * @param mixed|null $default
     * @return void
     */
    public function addOptionalOption(string $name, string $description = '', $default = null): void
    {
        $this->addOption($name,null,InputOption::VALUE_OPTIONAL,$description,$default);
    }

    /**
     * Run console command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {      
        $result = $this->executeCommand($input,$output);
        
        return (empty($result) == true) ? 0 : $result;
    }

    /**
     * Get table obj
     *
     * @return Symfony\Component\Console\Helper\Table
     */
    public function table()
    {
        return $this->table;
    } 

    /**
     * Set command as default
     *
     * @param boolean $default
     * @return void
     */
    public function setDefault(bool $default = true): void
    {
        $this->default = $default;
    }

    /**
     * Return true if command is default.
     *
     * @return boolean
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * Show command title
     *
     * @param string?null $title
     * @param string
     * @return void
     */
    public function showTitle(?string $title = null,string $space = ' '): void
    {
        if ($this->isConsoleOutput() == false) {
            $this->result['title'] = $title;
            return;
        }

        $title = $title ?? $this->getDescription();
        $title = ConsoleHelper::getDescriptionText($title);
        
        $this->style->newLine();
        $this->style->writeLn($space . $title);
        $this->style->newLine();
    }

    /**
     * Show error message
     *
     * @param string $message
     * @param string $label
     * @param string $space
     * @return void
     */
    public function showError(string $message, string $label = 'Error:', string $space = ' '): void
    {
        $this->style->newLine();
        $this->style->writeLn($space . '<error>' . $label  . ' ' . $message . '</error>');
        $this->style->newLine();
    }

    /**
     * Show multipel errors
     *
     * @param string|array $errors
     * @param string $label
     * @param string $space
     * @return void
     */
    public function showErrors($errors, string $label = 'Error:', string $space = ' '): void
    {
        if (\is_array($errors) == true) {
            foreach($errors as $error) {
                $this->showError($error,$label,$space);
            }
            return;
        }

        $this->showError($errors,$label,$space);
    }

    /**
     * Show error details
     *
     * @param string|array $details
     * @param string $space
     * @return void
     */
    public function showErrorDetails($details, $space = ' '): void
    {
        if (\is_array($details) == true) {
            foreach ($details as $item) {
                $this->style->writeLn($space . ConsoleHelper::errorMark() . ' ' . $item);
            }
            return;
        }

        $this->style->writeLn($space . ConsoleHelper::errorMark() . ' ' . $details);
    } 

    /**
     * Show CHECK MARK
     *
     * @param string $space
     * @return void
     */
    public function checkMark(string $space = ' '): void
    {
        $this->style->write(ConsoleHelper::checkMark($space));
    }

    /**
     * New line
     *
     * @return void
     */
    public function newLine(): void
    {
        $this->style->newLine();
    }

    /**
     * Show 'done' message
     *
     * @param string|null $label
     * @param string $space
     * @return void
     */
    public function showCompleted(?string $label = null,string $space = ' '): void
    {
        $label = (empty($label) == true) ? 'done.' : $label;           
        $this->style->newLine();
        $this->style->writeLn($space . '<fg=green>' . $label . '</>');
        $this->style->newLine();
    }

    /**
     * Write field
     *
     * @param string $label
     * @param mixed $value
     * @param string $color
     * @param string $space
     * @return void
     */
    public function writeField(string $label, $value,string $color = 'cyan',string $space = ' '): void
    {
        $this->style->write($space);
        $label = ConsoleHelper::getLabelText($label,$color);
        $this->style->write($label . ' ');
        $this->style->write($value);
    }

    /**
     * Write field
     *
     * @param string $label
     * @param mixed $value
     * @param string $color
     * @param string $space
     * @return void
     */
    public function writeFieldLn(string $label, $value,string $color = 'cyan',string $space = ' '): void
    {
        $this->writeField($label,$value,$color,$space);
        $this->newLine();
    }

    /**
     * Write line
     *
     * @param string $text
     * @param string $space
     * @param string? $color
     * @return void
     */
    public function writeLn(string $text, string $space = ' ', ?string $color = null): void
    {
        if (empty($color) == false) {
            $text = ConsoleHelper::getLabelText($text,$color);
        }
      
        $this->style->writeLn($space . $text);
    }
}
