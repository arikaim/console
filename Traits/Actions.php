<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Console\Traits;

use Arikaim\Core\Actions\Actions as Action;

/**
 * Actions trait
*/
trait Actions
{        
    /**
     * Run action
     *
     * @param string $actionClass
     * @param string $package
     * @param array  $params
     * @return void
     */
    public function runAction(string $actionClass, string $package, array $params): void
    {
        $action = Action::create($actionClass,$package,$params)->getAction();
        
        // check if action valid
        if ($action->hasError() == true) {
            $this->showError($action->getError());
            return;
        }

        // run action
        $action->run();

        if ($action->hasError() == true) {
            $this->showError($action->getError());
            return;           
        }       
        
        $this->showCompleted($action->get('message','Action executed successfully'));

        foreach ($action->getResult() as $key => $value) {
            $this->writeField($key,$value);
        }
    }
}
