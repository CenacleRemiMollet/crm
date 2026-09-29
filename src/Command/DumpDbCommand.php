<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'db:dump', description: 'Dump the dabatase', help: <<<'TXT'
This command dumps the database
TXT)]
class DumpDbCommand extends Command
{
	private $doctrine;

	public function __construct(?ManagerRegistry $doctrine = null)
	{
		parent::__construct();
		$this->doctrine = $doctrine;
	}

	protected function configure(): void
	{
		$this->addArgument('path', InputArgument::REQUIRED, 'Path to dump');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$conn = $this->doctrine->getConnection();

		$dir = $input->getArgument('path');
		if (! is_dir($dir)) {
			$fs = new Filesystem();
			$fs->mkdir($dir);
		}
		$now = new \DateTime();
		$path = $dir.DIRECTORY_SEPARATOR.'dump-'.$now->format('Ymd-His').'.sql';
		$output->writeln('Writing dump to '.$path);

		// no shell, no password on the command line nor in the output
		$params = $conn->getParams(); // DBAL 3: getUsername()/getPassword() no longer exist
		$args = ['mariadb-dump', '-u', $params['user'] ?? ''];
		if (! empty($params['host'])) {
			$args[] = '-h';
			$args[] = $params['host'];
		}
		if (! empty($params['port'])) {
			$args[] = '-P';
			$args[] = (string) $params['port'];
		}
		$args[] = '--result-file='.$path;
		$args[] = $conn->getDatabase();
		$process = new Process($args, null, ['MYSQL_PWD' => $params['password'] ?? '']);
		$process->setTimeout(600);
		$process->run();
		if (! $process->isSuccessful()) {
			$output->writeln('<error>'.$process->getErrorOutput().'</error>');
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

}

