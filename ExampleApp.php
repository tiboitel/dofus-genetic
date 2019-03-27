<?php

use pdeans\Http\Client as Client;

class ExampleApp
{
  const ITEMS_CATEGORIES = ["Bottes", "Anneau", "Amulette", "Cape", "Ceinture", "Chapeau"];

  public function start()
  {
    $client = new Client([
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    // Get all equipments.
    $response = $client->get('https://dofapitouch.herokuapp.com/equipments?filter[offset]=0&filter[limit]=2235&filter[skip]=0');
	// $response = $client->get("https://dofapi2.herokuapp.com/equipments?filter[offset]=0&filter[limit]=2912&filter[skip]=0");
    $items = json_decode($response->getBody(), TRUE);
    $equipments = [];
    // Sorted equipments on sub-arrays based on categorys.
    foreach (self::ITEMS_CATEGORIES as $type_name)
    {
      $equipments[$type_name] = array_filter($items, function ($item) use ($type_name)
      {
          return ($item['lvl'] <= 60 && $item['lvl'] >= 25 &&
              $item['type'] === $type_name);
        });
    }
	// How to use.
    $algorithm = new \DofusGenetic\SelectionEquipmentsGeneticAlgorithm($equipments);
	$algorithm->setWeight(['PA' => 0.50, 'PM' => 0.50, 'Force' => 0.85, 'Vitalité' => 0.20,'Sagesse' => 0.3, "Agilité" => 0.10, "Puissance" => 0.25]);
    $algorithm->setForbiddenEquipment([967, 968, 958, 11364, 12238, 12237, 10852]);
    $best_character = $algorithm->start()[0];
	// Example app.
	$total_stats = [];
	// For each item of the individual we're addition his stats.
	echo "<h3> Best generated character: </h3>";
	echo "<pre>";
	foreach ($best_character as $item)
	{
		echo $item['name'] . "</br>";
		$item = \DofusGenetic\Helpers\ItemHelper::getStats($item);
		foreach ($item as $key => $value)
			$total_stats[$key] = (isset($total_stats[$key])) ?
				$total_stats[$key] + $value : $value;
	}
	echo "</br>";
	foreach ($total_stats as $key => $value)
		echo $key . ": " . $value . "</br>";
	echo "<h3>Top items for each slots:</h3>";
	foreach (self::ITEMS_CATEGORIES as $tmp)
	{
		$top_items = $algorithm->getTopItems($tmp, 10);
		for ($i = 0; $i < 10; $i++)
			echo $top_items[$i]['name'] . ' - ';
		echo "<br />";
	}
	echo "</pre>";
  }
}
