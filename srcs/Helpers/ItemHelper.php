<?php

namespace DofusGenetic\Helpers;

class ItemHelper
{
	// Vit, PA, PM, Init, Pro, PO, Inv, Sag, For, Int Cha, Agi, Dom
	const STATS_NAME = [
		"Vitalité",
		"PA",
		"PM",
		"Initiative",
		"Prospection",
		"PO",
		"Invocations",
		"Sagesse",
		"Force",
		"Intelligence",
		"Chance",
		"Agilité",
		"Soins",
		"Dommages",
		"Puissance",
		"Dommages Critiques",
		"Dommages Neutre",
		"Dommages Terre",
		"Dommages Feu",
		"Dommages Eau",
		"Dommages Air",
		"Dommages Poussée",
		"Dommages Pièges",
		"Renvoie dommages",
		"Puissance (pièges)",
		"Résistance Neutre",
		"Résistance Terre",
		"Résistance Feu",
		"Résistance Eau",
		"Résistance Air",
		"Résistance Critiques",
		"Résistance Poussée",
		"% Résistance Neutre",
		"% Résistance Terre",
		"% Résistance Feu",
		"% Résistance Eau",
		"% Résistance Air"
	];

	static public function getStats($equipment)
	{
		$tmp = [];
		foreach (self::STATS_NAME as $stat_name)
		{
			$tmp[$stat_name] = 0;
			foreach ($equipment['stats'] as $stat)
			{
				if (array_key_exists($stat_name, $stat))
					$tmp[$stat_name] = (array_key_exists("to", $stat[$stat_name])) ?
							$stat[$stat_name]["to"] : $stat[$stat_name]["from"];
			}
		}
		return ($tmp);
	}

	static public function normalizeItem($equipment)
	{
		$equipment['PA'] *= 85;
		$equipment['PM'] *= 75;
		$equipment['PO'] *= 41;
		$equipment['Sagesse'] *= 3;
		$equipment ['Dommages'] *= 20;
		$equipment['Prospection'] *= 3;
		$equipment['Invocations'] *= 30;
		$equipment['Puissance'] *= 2;
		$equipment["Résistance Neutre"] *= 2;
		$equipment["Résistance Terre"] *= 2;
		$equipment["Résistance Feu"] *= 2;
		$equipment["Résistance Eau"] *= 2;
		$equipment["Résistance Air"] *= 2;
		$equipment["Résistance Critiques"] *= 2;
		$equipment["Résistance Poussée"] *= 2;
		$equipment["% Résistance Neutre"] *= 4;
		$equipment["% Résistance Terre"] *= 4;
		$equipment["% Résistance Feu"] *= 4;
		$equipment["% Résistance Eau"] *= 4;
		$equipment["% Résistance Air"] *= 4;
		$equipment["Initiative"] *= 0.5;
		return ($equipment);
	}
}
 ?>
