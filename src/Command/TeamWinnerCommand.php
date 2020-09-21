<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Desc - Command to check whether a team can win by rearraging players against other team or not.
 */
class TeamWinnerCommand extends Command
{
    /** Constants **/
    const COMMAND_NAME = 'app:find-team-wining-chance';
    const RE = '/^\d+(?:,\d+)*$/';

    /**
     * Desc - Configure function
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            // The short description shown while running "php bin/console list"
            ->setName(self::COMMAND_NAME)
            ->setDescription('Checks whether a team can win by rearraging players against other team or not.')
            ->addOption(
                'team_a_values',
                null,
                InputOption::VALUE_REQUIRED,
                'Team - A values '
            )
            ->addOption(
                'team_b_values',
                null,
                InputOption::VALUE_REQUIRED,
                'Team - B values '
            );
    }

    /**
     * Desc - This optional method is the first one executed for a command after configure()
     *        and is useful to initialize properties based on the input arguments and options.
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Desc - This method is executed after initialize() and before execute(). Its purpose
     *        is to check if some of the options/arguments are missing and interactively
     *        ask the user for those values.
     **/
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('team_a_values') && null !== $input->getOption('team_b_values')) {
            return;
        }

        $this->io->title('Input Team Scores Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            '$php bin/console app:find-team-wining-chance --team_a_values=35,100,20,50,40 --team_b_values=35,10,30,20,90',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the team A score values if it's not defined
        $teamAValues = $input->getOption('team_a_values');
        if (null !== $teamAValues) {
            $this->io->text(' > <info>Team A players: </info>:' . $teamAValues);
        } else {
            $teamAValues = $this->io->ask('Enter team A players');
            $input->setOption('team_a_values', $teamAValues);
        }

        // Ask for the team B score values if it's not defined
        $teamBValues = $input->getOption('team_b_values');
        if (null !== $teamBValues) {
            $this->io->text(' > <info>Team B players: </info>:' . $teamBValues);
        } else {
            $teamBValues = $this->io->ask('Enter team B players');
            $input->setOption('team_b_values', $teamBValues);
        }
    }

    /**
     * Desc - Main execute function , it usually contains the logic to execute to 
     *        complete this command task.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $teamAValuesInput = $input->getOption('team_a_values');
        $teamBValuesInput = $input->getOption('team_b_values');

        // Using comma separated string for array input.
        $formattedTeamAInput = array_filter(explode(',', $teamAValuesInput));
        $formattedTeamBInput = array_filter(explode(',', $teamBValuesInput));

        // Check few conditions before moving for processing.
        if (empty($formattedTeamAInput)) {
            $output->writeln("<error>Team A input values cannot be empty !</error>");

            return Command::FAILURE;
        }
        if (empty($formattedTeamBInput)) {
            $output->writeln("<error>Team B input values cannot be empty !</error>");

            return Command::FAILURE;
        }
        // Regex check for allowing only comma separated input.
        if (!(preg_match(self::RE, $teamAValuesInput))) {
            $output->writeln("<error>Please only enter comma separated input values for Team A!</error>");

            return Command::FAILURE;
        }
        if (!(preg_match(self::RE, $teamBValuesInput))) {
            $output->writeln("<error>Please only enter comma separated input values for Team B!</error>");

            return Command::FAILURE;
        }
        // Size check.
        if (count($formattedTeamAInput) != count($formattedTeamBInput)) {
            $output->writeln("<error>No. of players in both the teams should be equal !</error>");
            $output->writeln("<info>Team A - " . $teamAValuesInput . "</info>");
            $output->writeln("<info>Team B - " . $teamBValuesInput . "</info>");

            return Command::FAILURE;
        }

        $teamCanAWin = $this->checkTeamWin($formattedTeamAInput, $formattedTeamBInput);
        if ($teamCanAWin) {
            $output->writeln("<info>Team A can win !!!</info>");
        } else {
            $output->writeln("<error>Team A can't win !!!</error>");
        }

        return Command::SUCCESS;
    }

    /**
     * Desc - Checks whether Team A can win or not.
     *
     * @param array $teamA
     * @param array $teamB
     * 
     * @return boolean
     */
    private function checkTeamWin(array $teamA, array $teamB): bool
    {
        $resultArr =  array();
        sort($teamA);
        $totalPlayers = count($teamA);
        // Start process.
        if (max($teamB) < $teamA[$totalPlayers - 1]) {
            for ($i = 0; $i < $totalPlayers; $i++) {
                if (!empty($teamA)) {
                    $foundGreater = $this->findNextGreater($teamA, $teamB[$i]);
                    if ($foundGreater == -1) {
                        // Case when no greater element left for current B's value.
                        break;
                        return false;
                    } else {

                        $resultArr[] = $teamA[$foundGreater];
                        // Remove the element once used in array
                        unset($teamA[$foundGreater]);
                        $teamA = array_values($teamA);
                    }
                }
            }

            return true;
        } else {
            // Case when B is having a elem , greater than all A elem's.
            return false;
        }
    }

    /**
     * Desc - Get's the next greater element for a given number in an array. 
     *
     * @param array $arr
     * @param integer $target
     * 
     * @return integer
     */
    private function findNextGreater(array &$arr, int $target): int
    {
        // Default values setup.
        $low = 0;
        $mid = -1;
        $high = count($arr);
        while ($low != $high) {
            $mid = ($low + $high) / 2;
            if ($arr[$mid] <= $target) {
                $low = $mid + 1;
            } else {
                $high = $mid;
            }
        }

        return (int)$mid;
    }
}