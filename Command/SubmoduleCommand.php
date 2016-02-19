<?php

namespace CanalTP\PHPGit\Command;

use PHPGit\Command;

/**
 * Submodule command tagger
 * @package CanalTP\PHPGit
 */
class SubmoduleCommand extends Command
{
    /**
     * @return array
     */
    public function __invoke()
    {
        $builder = $this->git->getProcessBuilder()
            ->add('submodule');

        $submodules = array();
        $output  = $this->git->run($builder->getProcess());
        $lines   = $this->split($output);

        foreach ($lines as $line) {
            $matches = array();
            if (preg_match('/^(\+?)(.*)\s(.*)\s\((.*)\)$/', $line, $matches)) {
                $git = new Git();
                $git->setRepository($this->git->getRepository().'/'.$matches[3]);
                $submodules[] = array(
                    'modified' => $matches[1] === '+',
                    'path' => $matches[3],
                    'repository' => $git,
                );
            }
        }

        return $submodules;
    }
}
