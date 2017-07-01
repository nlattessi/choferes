<?php

// composer require illuminate/database
// composer require league/csv
// {
//     "require": {
//         "illuminate/database": "^5.4",
//         "league/csv": "^8.2"
//     }
// }

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'choferes',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;
// $capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

class Chofer extends Illuminate\Database\Eloquent\Model {
    protected $table = 'chofer';

    public function choferCursos()
    {
        return $this->hasMany('ChoferCurso');
    }
}

class ChoferCurso extends Illuminate\Database\Eloquent\Model {
    protected $table = 'chofer_curso';
}

$csv = \League\Csv\Writer::createFromPath(new SplFileObject('/tmp/chofer.csv', 'a+'), 'w');

$csv->insertOne(['id_chofer', 'nombre', 'apellido', 'dni', 'id_cursos_asociado']);

$choferesRepetidos = Chofer::whereIn('dni', function ($query) {
    $query->select('dni')->from('chofer')->groupBy('dni')->havingRaw('count(*) > 1');
})->has('choferCursos')->orderBy('dni')->get();

$choferesRepetidos->each(function($chofer) use ($csv) {
    $csv->insertOne([$chofer->id, $chofer->nombre, $chofer->apellido, $chofer->dni, $chofer->choferCursos->implode('id', ', ')])    ;
});

// $csv->output('choferes.csv');

// echo "Total choferes: " . $choferesRepetidos->count() . "\n";

// foreach ($choferesRepetidos->take(100) as $chofer) {
//     echo "ID: {$chofer->id}\n";
// }

// $deleteSQL = Chofer::whereIn('dni', function ($query) {
//     $query->select('dni')->from('chofer')->groupBy('dni')->havingRaw('count(*) > 1');
// })->doesntHave('choferCursos')->toSql();

// echo $deleteSQL;
