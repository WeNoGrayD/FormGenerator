<?php

include '.\my_components_classlib.php';

/*
	Простая форма, сгенерированная при помощи генератора форм.
 */

if (isset($_GET['languages'])) 
{
	$languages = $_GET['languages'];
	echo "You can code programs on " . join(', ', $languages) . '.'; 
	echo '<br>';
}

if (isset($_GET['harrypotter']))
{
	$harrypotter = $_GET['harrypotter'];
	$manybooks = count($harrypotter) > 1;
	echo "Your beloved book" . ($manybooks ? 's' : ''); 
	echo " of Harry Potter Saga " . ($manybooks ? 'are ' : 'is ') .join(', ', $harrypotter) . '!';
	echo '<br>';
}

if (isset($_GET['starwars']))
{
	$starwars = $_GET['starwars'];
	echo "You hate $starwars"."th film from Star Wars Saga so much! Very good!";
	echo '<br>';
}

echo '<br><br>';


$form_gener = new FormGeneratorExample();
$form_gener->GenerateNew();

?>