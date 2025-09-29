<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:backfill-uuids')]
class BackfillUuidsCommand extends Command
{
	public function __construct(private EntityManagerInterface $em)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		foreach ([User::class, Vehicle::class] as $class) {
			$repo = $this->em->getRepository($class);
			$iter = $repo->createQueryBuilder('e')->where('e.uuid IS NULL')->getQuery()->toIterable();

			$count = 0;
			foreach ($iter as $e) {
				if (method_exists($e, 'setUuid')) {
					$e->setUuid(Uuid::v7());
				} else {
					$rp = new \ReflectionProperty($class, 'uuid');
					$rp->setAccessible(true);
					$rp->setValue($e, Uuid::v7());
				}
				if ((++$count % 500) === 0) {
					$this->em->flush();
					$this->em->clear();
				}
			}
			$this->em->flush();
			$output->writeln(sprintf('%s: %d lignes mises à jour', (new \ReflectionClass($class))->getShortName(), $count));
		}
		return Command::SUCCESS;
	}
}
