 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require './vendor/autoload.php';
require './ExampleApp.php';

$app = new ExampleApp();
$app->start();
