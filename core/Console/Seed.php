<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Seed extends Command
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'seed';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'seeder',
				InputArgument::REQUIRED,
				'Seeder class',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$seeder = $input->getArgument( 'seeder' );

		$seederNamespace = 'Databases\\Seeders\\' . $seeder;
		$seederClass     = new $seederNamespace();

		if ( method_exists( $seederClass, 'run' ) ) {
			$seederClass->run();

			$io->success( 'Database was successfully seeded' );
			return CreateClassCommand::SUCCESS;
		}

		$io->warning( 'Seeder ' . $seederNamespace . ' does not have `run()` method' );
		return CreateClassCommand::FAILURE;
	}

}
