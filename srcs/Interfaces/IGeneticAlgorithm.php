<?php

namespace DofusGenetic\Interfaces;

interface IGeneticAlgorithm
{
  public function generate_population();
  public function fitness_function($item);
  public function selection();
  public function mutation();
  public function crossover();
}
