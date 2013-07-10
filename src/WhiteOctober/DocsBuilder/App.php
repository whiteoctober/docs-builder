<?php

namespace WhiteOctober\DocsBuilder;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class App extends Application
{
    protected $calledFrom = "";

    public function getCalledFrom()
    {
        return $this->calledFrom;
    }

    public function setCalledFrom($calledFrom)
    {
        $this->calledFrom = $calledFrom;
    }

    protected function getCommandName(InputInterface $input)
    {
        // This should return the name of your command.
        return 'wo:docs-build';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new Command\DoBuildCommand();

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}

