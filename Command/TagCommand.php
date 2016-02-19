<?php

namespace CanalTP\PHPGit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CanalTP\PHPGit\Git;

/**
 * Tag command
 * @package CanalTP\PHPGit\Command
 */
class TagCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('tag')
            ->setDescription('Tag application and submodules')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Repository URL'
            )
            ->addArgument(
                'branch',
                InputArgument::REQUIRED,
                'Your branch to tag'
            )
            ->addArgument(
                'tag',
                InputArgument::REQUIRED,
                'Tag name'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $input->getArgument('url');
        $branch = $input->getArgument('branch');
        $tag = $input->getArgument('tag');

        $repositoryPath = __DIR__.'/../build/'.$tag;

        $git = new Git();

        $this->title($output, 'CLONE');
        $git->clone($repo, $repositoryPath);
        $git->setRepository($repositoryPath);
        $git->fetch('origin', $branch.':'.$branch);
        $git->checkout($branch);

        $this->title($output, 'SELECTION DU TAG D\'INIT DES SOUS MODULES (master si ancien module)');
        $git->execute('submodule update --init');
        $git->execute(array('submodule', 'foreach', 'git checkout '.$branch.' || :'));

        $this->title($output, 'TAG DES SOUS MODULES');
        $modifiedSubmodules = array();
        $submodules = $git->submodule();
        foreach ($submodules as $submodule) {
            if ($submodule['modified']) {
                $modifiedSubmodules[] = $submodule['path'];
                $subGit = $submodule['repository'];
                $subGit->checkout('master');
                $subGit->merge($branch, 'Auto merge PHP', ['no-ff' => true]);
                $subGit->tag->create($tag);
                $subGit->push('origin', 'master', array('tags' => true));
            }
        }

        $this->title($output, 'TAG DE L\'APPLICATION');
        $git->add($modifiedSubmodules);
        $git->commit('Refs pour release  '.$tag);
        $git->checkout("master");
        $git->merge($branch, 'Auto merge PHP', ['no-ff' => true]);
        $git->tag->create($tag);
        $git->push('origin', 'master', array('tags' => true));
    }

    protected function title(OutputInterface $output, $text)
    {
        $output->writeln('<info>'.$text.'</info>');
    }

    protected function command(OutputInterface $output, $text)
    {
        $output->writeln('<comment>'.$text.'</comment>');
    }

    protected function output(OutputInterface $output, $text)
    {
        $output->writeln($text);
    }

    protected function error(OutputInterface $output, $text)
    {
        $output->writeln('<error>'.$text.'</error>');
    }
}
