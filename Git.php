<?php

namespace CanalTP\PHPGit;

use PHPGit\Git as baseGit;
use Symfony\Component\Process\Process;
use CanalTP\PHPGit\Command\SubmoduleCommand;

/**
 * Git class
 * @package CanalTP\PHPGit
 */
class Git extends baseGit
{
    /**
     * @var SubmoduleCommand
     */
    public $submodule;

    /**
     * @var string
     */
    private $directory = '.';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->submodule = new SubmoduleCommand($this);
    }

    /**
     * {@inheritdoc}
     */
    public function setRepository($directory)
    {
        $this->directory = $directory;

        return parent::setRepository($directory);
    }

    /**
     * Get the Git repository path
     *
     * @return string
     */
    public function getRepository()
    {
        return $this->directory;
    }

    /**
     * @param mixed $command
     * @return mixed
     */
    public function execute($command)
    {
        $builder = $this->getProcessBuilder()
                        ->setTimeout(300);
        if (is_array($command)) {
            $parts = $command;
        } else {
            $parts = explode(' ', $command);
        }
        foreach ($parts as $part) {
            $builder->add($part);
        }
        $process = $builder->getProcess();

        return $this->run($process);
    }

    /**
     * {@inheritdoc}
     */
    public function run(Process $process)
    {
        echo $process->getCommandLine()."\n";

        return parent::run($process);
    }
}
