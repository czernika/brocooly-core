<?php
/**
 * Clear folder with all cached view files
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearCache extends Command
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'cache:flush';

	/**
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {

		$io = new SymfonyStyle( $input, $output );

		$loader = new \Timber\Loader();
		$loader->clear_cache_twig();
		$loader->clear_cache_timber();

		// Output
		$io->success( 'Cache was successfully flushed' );

		return Command::SUCCESS;
	}

}
