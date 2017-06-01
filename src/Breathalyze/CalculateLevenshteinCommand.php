<?php
namespace Breathalyze;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateLevenshteinCommand extends Command
{
	const VOCABULARY_FILE = "vocabulary.txt";

	/**
	 * @var array
	 */
	private $vocabulary = [];

	protected function configure()
	{
		$this->setName('breathalyze');
		$this->setDescription('Calculate levenshtein distance on vocabulary');
		$this->setHelp("See Description.docx");
		$this->addArgument('input_filename', InputArgument::REQUIRED, 'File with input words to calculate number of changes ');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (extension_loaded('xdebug')) {
			$output->writeln("<comment>Please disable xdebug to improve script performance</comment>");
		}

		$inputFilename = $input->getArgument('input_filename');

		if (!is_file($inputFilename) || !is_readable($inputFilename)) {
			$output->writeln('<error>Not found input file or it is not readable</error>');
			return 1;
		}

		$inputContent = file_get_contents($inputFilename);

		preg_match_all('/(\S+)/', $inputContent, $matches);
		$searchArray = $matches[0];
		$searchArray = array_map(function ($var) {
			return strtoupper($var);
		}, $searchArray);

		if (0 === count($searchArray)) {
			$output->writeln(0);
			return 0;
		}

		if (!$this->loadVocabularyData()) {
			$output->writeln('<error>Failed to load vocabulary file or it is not readable</error>');
			return 1;
		}

		$lengthsSum = 0;
		$keys = [];

		foreach ($searchArray as $item) {
			$itemLength = strlen($item);
			if (!array_key_exists($itemLength, $this->vocabulary)) {
				$this->vocabulary[$itemLength] = [];
				$keys = [];
			}

			if (array_key_exists($item, $this->vocabulary[$itemLength])) {
				$lengthsSum += $this->vocabulary[$itemLength][$item];
				continue;
			}

			if (!array_key_exists($itemLength, $keys)) {
				$keys[$itemLength] = $this->getIterateKeys(array_keys($this->vocabulary), $itemLength);
			}

			$levenshtein = 999;

			foreach ($keys[$itemLength] as $key) {
				$diff = $key - strlen($item);
				if ($diff < 0) {
					$diff *= -1;
				}
				if ($diff >= $levenshtein) {
					continue;
				}
				foreach ($this->vocabulary[$key] as $content => $value) {
					$levenshteinNew = levenshtein($content, $item) + $value;
					if ($levenshteinNew < $levenshtein) {
						$levenshtein = $levenshteinNew;

						if ($levenshtein === 1) {
							break 2;
						}
					}
				}
			}

			$lengthsSum += $levenshtein;
			$this->vocabulary[$itemLength][$item] = $levenshtein;
		}

		$output->writeln($lengthsSum);

		return 0;
	}

	/**
	 * Filling vocabulary from file
	 *
	 * @return bool
	 */
	private function loadVocabularyData()
	{
		if (!is_file(self::VOCABULARY_FILE) || !is_readable(self::VOCABULARY_FILE)) {
			return false;
		}

		foreach (array_filter(explode("\n", file_get_contents(self::VOCABULARY_FILE))) as $value) {
			$this->vocabulary[strlen($value)][$value] = 0;
		}

		return true;
	}


	/**
	 * Get key list to iterate over
	 *
	 * @param array $keys
	 * @param $length
	 * @return array
	 */
	private function getIterateKeys(array $keys, $length)
	{
		$result = [];
		$max = max($length, max($keys));
		if (in_array($length, $keys)) {
			$result[] = $length;
		}
		for ($i = 1; $i <= $max; $i++) {
			if (in_array($length - $i, $keys)) {
				$result[] = $length - $i;
			}
			if (in_array($length + $i, $keys)) {
				$result[] = $length + $i;
			}
		}
		return $result;
	}
}