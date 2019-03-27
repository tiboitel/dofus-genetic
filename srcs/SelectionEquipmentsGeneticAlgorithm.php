<?php
namespace DofusGenetic;

use DofusGenetic\Interfaces;
use DofusGenetic\Helpers;

class SelectionEquipmentsGeneticAlgorithm implements \DofusGenetic\Interfaces\IGeneticAlgorithm
{
	const MAX_CHARACTERS_IN_POOL = 5000;
	const MAX_ELITE_CLONES = 25;
	const MUTATION_PERCENT_CHANCE = 5;
	const MAX_CHILDREN_PER_COUPLE = 3;

	private $population = [];
	private $items = [];
	private $forbidden_equipment = [];
	// 	Vit, PA, PM, Init, Pro, PO, Inv, Sag, For, Int Cha, Agi, Dom
	private $weight = [
		"Vitalité" => 0.10,
		"PA" => 0.17,
		"PM" => 0.17,
		"Initiative" => 0.01,
		"Prospection" => 0.01,
		"PO" => 0.12,
		"Invocations" => 0.01,
		"Sagesse" => 0.01,
		"Force" => 0.01,
		"Intelligence" => 0.01,
		"Chance" => 0.01,
		"Agilité" => 0.01,
		"Soins" => 0.01,
		"Dommages" => 0.20,
		"Puissance" => 0.15,
		"Dommages Critiques" => 0.01,
		"Dommages Neutre" => 0.01,
		"Dommages Terre" => 0.01,
		"Dommages Feu" => 0.01,
		"Dommages Eau" => 0.01,
		"Dommages Air" => 0.01,
		"Dommages Poussée" => 0.01,
		"Dommages Pièges" => 0.01,
		"Renvoie dommages" => 0.01,
		"Puissance (pièges)" => 0.01,
		"Résistance Neutre" => 0.1,
		"Résistance Terre" => 0.1,
		"Résistance Feu" => 0.1,
		"Résistance Eau" => 0.1,
		"Résistance Air" => 0.1,
		"Résistance Critiques" => 0.1,
		"Résistance Poussée" => 0.1,
		"% Résistance Neutre" => 0.1,
		"% Résistance Terre" => 0.1,
		"% Résistance Feu" => 0.1,
		"% Résistance Eau" => 0.1,
		"% Résistance Air" => 0.1
	];

	private static function sort_by_weight($a, $b)
	{
		return -($a['weight'] <=> $b['weight']);
	}

	public function __construct(array $items)
	{
		$this->items = $items;
	}

	public function setForbiddenEquipment(array $ids) : self
	{
		$this->forbidden_equipment = $ids;
		return ($this);
	}

	public function getForbiddenEquipment() : array
	{
		return ($this->forbidden_equipment);
	}

	public function setWeight(array $weight) : self
	{
		foreach ($weight as $key => $value)
		{
			if (array_key_exists($key, $this->weight))
				$this->weight[$key] = $value;
		}
		return ($this);
	}

	public function getTopItems($categorie, $offset) : array
	{
		$top_items = [];
		foreach ($this->items as $key => $categories)
		{
			foreach ($categories as $id => $item)
			{
				$item = \DofusGenetic\Helpers\ItemHelper::getStats($item);
				$this->items[$key][$id]['weight'] = $this->fitness_function($item);
			}
			usort($this->items[$key], "static::sort_by_weight");
		}
		for ($i = 0; $i < $offset; $i++)
			$top_items[] = $this->items[$categorie][$i];
		return ($top_items);
	}

	public function start()
	{
		// Population test
		$this->generate_population();
		for ($i = 0; $i < 25; $i++)
		{
			$this->selection();
			$this->crossover();
		}
		return ($this->population);
	}

	public function generate_population()
	{
		for ($i = 0; $i < self::MAX_CHARACTERS_IN_POOL; $i++)
		$this->population[$i] = $this->generate_individual();
	}

	public function fitness_function($item)
	{
		$fitness = 0.00;
		// Need to store this into a json file.
		$item = \DofusGenetic\Helpers\ItemHelper::normalizeItem($item);
		if (($sum = array_sum($item)) <= 0)
		return (0);
		foreach ($item as $key => $stat)
		$fitness += ($stat > 0) ? ($stat * $this->weight[$key]) : ($stat / $this->weight[$key]);
		return ($fitness);
	}

	public function selection()
	{
		$fitness_array = [];
		// For each individual in the population
		foreach ($this->population as $individual)
		{
			$total_stats = [];
			// For each item of the individual we're addition his stats.
			foreach ($individual as $item)
			{
				$item = \DofusGenetic\Helpers\ItemHelper::getStats($item);
				foreach ($item as $key => $value)
				$total_stats[$key] = (isset($total_stats[$key])) ?
				$total_stats[$key] + $value : $value;
			}
			// Calculation of the fitness of the individual.
			$fitness_array[] = array('individual' => $individual, 'weight' => $this->fitness_function($total_stats));
		}
		$this->population = [];
		// Get the fitness weight of a random individual of the population.
		$fitness_sum = $fitness_array[rand(0, count($fitness_array) - 1)]['weight'];
		// Get a random value between 0 and the fitness value selected.
		$rand = rand(0, $fitness_sum);
		// If the current invidual have a weight higher than selected fitness,
		// add it to the next population.
		for ($i = count($fitness_array) - 1; $i > 0; $i--)
		{
			if ($fitness_array[$i]['weight'] > $fitness_sum)
			$this->population[] = $fitness_array[$i]['individual'];
			$rand += $fitness_array[$i]['weight'];
		}
		// Sort the previous population by fitness weight.
		usort($fitness_array, "static::sort_by_weight");
		// Get n indivual with the better fitness (elite) and adding it
		// to the next population.
		for ($i = 0; $i < self::MAX_ELITE_CLONES; $i++)
		$this->population[] = $fitness_array[$i]['individual'];
		shuffle($this->population);
	}

	public function mutation()
	{

	}

	// Parent A : AABB
	// Parent B : CCDD
	// Child : ACBD - CADD - AADD etc...
	public function crossover()
	{
		$breeders = count($this->population);
		$childrens = [];
		for ($i = 0; $i < $breeders / 2; $i++)
		{
			for ($j = 0; $j < self::MAX_CHILDREN_PER_COUPLE && (count($childrens) - 1 +
			$breeders < self::MAX_CHARACTERS_IN_POOL); $j++)
			{
				$children = [];
				foreach ($this->population[$i] as $key => $item)
				{
					$children[$key] = (($rand = rand(1, 100)) < 50) ? $item :
					$this->population[$breeders - 1 - $i][$key];
				}
				$childrens[] = $children;
			}
		}
		$this->population = array_merge($this->population, $childrens);
	}

	private function generate_individual()
	{
		$individual = [];
		foreach ($this->items as $key => $values)
		{
			while (empty($individual[$key]) || in_array($individual[$key]['_id'],
			$this->forbidden_equipment))
			$individual[$key] = $this->items[$key][array_rand($this->items[$key], 1)];
		}
		return ($individual);
	}
}
