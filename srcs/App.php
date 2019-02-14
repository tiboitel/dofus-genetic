<?php
namespace DofusGenetic;

use pdeans\Http\Client as Client;
use DofusGenetic\Helpers;

class App
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
    $items = json_decode($response->getBody(), TRUE);
    $equipments = [];

    // Sorted equipments on sub-arrays based on categorys.
    foreach (self::ITEMS_CATEGORIES as $type_name)
    {
      $equipments[$type_name] = array_filter($items, function ($item) use ($type_name)
      {
          return ($item['lvl'] <= 87 && $item['lvl'] >= 35 &&
              $item['type'] === $type_name);
        });
    }
    $algorithm = new SelectionEquipmentsGeneticAlgorithm($equipments);
  }
}
