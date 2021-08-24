<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\InputArgument;

class DumpDbCommand extends Command
{
	// the name of the command (the part after "bin/console")
	protected static $defaultName = 'db:dump';

	private $doctrine;

	public function __construct(ManagerRegistry $doctrine = null)
	{
		parent::__construct();
		$this->doctrine = $doctrine;
	}

	protected function configure()
	{
		$this->addArgument('path', InputArgument::REQUIRED, 'Path to dump')
			->setDescription('Dump the dabatase')
			->setHelp('This command dumps the database');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$conn = $this->doctrine->getConnection();

		$path = $input->getArgument('path');
		if (! is_dir(dirname($path))) {
			$fs = new Filesystem();
			$fs->mkdir(dirname($path));
		}
		$now = new \DateTime();
		$path = $path.DIRECTORY_SEPARATOR.'dump-'.$now->format('Ymd-His').'.sql';
		$output->writeln('Writing dump to '.$path);

		$cmd = sprintf('mysqldump -u %s --password=%s %s > %s',
			$conn->getUsername(),
			$conn->getPassword(),
			$conn->getDatabase(),
			$path
			);
		$output->writeln($cmd);
		exec($cmd, $output, $exit_status);

		return Command::SUCCESS;
	}

}

