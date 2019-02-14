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
  // 	Vit, PA, PM, Init, Pro, PO, Inv, Sag, For, Int Cha, Agi, Dom
  private $weight = [
    "Vitalité" => 0.01,
    "PA" => 0.10,
    "PM" => 0.10,
    "Initiative" => 0.05,
    "Prospection" => 0.01,
    "PO" => 0.01,
    "Invocations" => 0.01,
    "Sagesse" => 0.18,
    "Force" => 0.01,
    "Intelligence" => 0.66,
    "Chance" => 0.66,
    "Agilité" => 0.40,
    "Soins" => 0.01,
    "Dommages" => 0.15,
    "Puissance" => 0.15,
    "Dommages Critiques" => 0.01,
    "Dommages Neutre" => 0.01,
    "Dommages Terre" => 0.01,
    "Dommages Feu" => 0.15,
    "Dommages Eau" => 0.01,
    "Dommages Air" => 0.15,
    "Dommages Poussée" => 0.01,
    "Dommages Pièges" => 0.01,
    "Renvoie dommages" => 0.01,
    "Puissance (pièges)" => 0.01,
    "Résistance Neutre" => 0.01,
    "Résistance Terre" => 0.01,
    "Résistance Feu" => 0.01,
    "Résistance Eau" => 0.01,
    "Résistance Air" => 0.01,
    "Résistance Critiques" => 0.08,
    "Résistance Poussée" => 0.08,
    "% Résistance Neutre" => 0.08,
    "% Résistance Terre" => 0.08,
    "% Résistance Feu" => 0.08,
    "% Résistance Eau" => 0.08,
    "% Résistance Air" => 0.08
  ];

  private static function sort_by_weight($a, $b)
  {
      return -($a['weight'] <=> $b['weight']);
  }

  public function __construct(array $items)
  {
      $this->items = $items;
      $i = 0;
      // Item recommandation by stats.
      foreach ($this->items as $key => $collection)
      {
        $fitness_array = [];
        foreach ($collection as $item)
        {
          $fitness_array[$i]['name'] = $item['name'];
          $item = \DofusGenetic\Helpers\ItemHelper::getStats($item);
          $fitness_array[$i]['weight'] = $this->fitness_function($item);
          $i++;
        }
        usort($fitness_array, 'static::sort_by_weight');
        echo "<h3>" . $key . "</h3>";
        echo "<div>";
        for ($i = 0; $i < 5; $i++)
        {
          echo $fitness_array[$i]['name'] . " - " . $fitness_array[$i]['weight'] . "</br>";
        }
        echo "</div>";
    }

    // Population test
    $this->generate_population();
    for ($i = 0; $i < 25; $i++)
    {
      echo "</br> Gen #" . $i . " ";
      $this->selection();
      $this->crossover();


    }
    echo "<pre>";
    $total_stats = [];
    // For each item of the individual we're addition his stats.
    foreach ($this->population[0] as $item)
    {
        echo $item['name'] . "</br>";
        $item = \DofusGenetic\Helpers\ItemHelper::getStats($item);
        foreach ($item as $key => $value)
        {
          $total_stats[$key] = (isset($total_stats[$key])) ?
              $total_stats[$key] + $value : $value;
        }
    }
    echo "</pre>";
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
    $item['PA'] *= 85;
    $item['PM'] *= 75;
    $item['PO'] *= 41;
    $item['Sagesse'] *= 3;
    $item ['Dommages'] *= 20;
    $item['Prospection'] *= 3;
    $item['Invocations'] *= 30;
    $item['Puissance'] *= 2;
    $item["Résistance Neutre"] *= 2;
    $item["Résistance Terre"] *= 2;
    $item["Résistance Feu"] *= 2;
    $item["Résistance Eau"] *= 2;
    $item["Résistance Air"] *= 2;
    $item["Résistance Critiques"] *= 2;
    $item["Résistance Poussée"] *= 2;
    $item["% Résistance Neutre"] *= 4;
    $item["% Résistance Terre"] *= 4;
    $item["% Résistance Feu"] *= 4;
    $item["% Résistance Eau"] *= 4;
    $item["% Résistance Air"] *= 4;
    $item["Initiative"] /= 2;
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
      $number_of_childrens = 3;
      $childrens = [];
      for ($i = 0; $i < $breeders / 2; $i++)
      {
          for ($j = 0; $j < $number_of_childrens && (count($childrens) - 1 + $breeders < self::MAX_CHARACTERS_IN_POOL); $j++)
          {
            $children = [];
            foreach ($this->population[$i] as $key => $item)
            {
              if (($rand = rand(1, 100)) < 50)
              {
                  $children[$key] = $item;
              }
              else {
                $children[$key] = $this->population[$breeders - 1 - $i][$key];
              }
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
       $individual[$key] = $this->items[$key][array_rand($this->items[$key], 1)];
    return ($individual);
  }
}
