<?php

/*
	Пакет, предоставляющий специфицированные компоненты для генерации форм.
 */
 
include '.\form_generator_classlib.php';

// Пример наследования класса фабрики формы.
class FormFactoryGET extends FormFactory
{
	// Конструктор.
	public function __construct($form_action)
	{
		parent::__construct(form_action: $form_action, form_method: 'GET');
	}
}

// Пример наследования абстрактного класса фабрики контрола.
class SelectFactoryExample extends SelectFactory
{
	// Конструктор.
	public function __construct()
	{
		parent::__construct(
			multiple_select: true,
			multiple_return: true, 
			visible_size: 10);
	}
	
	/*
	// Конструктор.
	public function __construct()
	{
		parent::__construct(
			multiple_select: true, 
			autofocus: false, 
			require_select: false, 
			visible_size: 10,
			is_disabled: false, 
			multiple_return: true);
	}
	*/
}

// Пример наследования абстрактного класса фабрики контрола.
class SelectFactoryHarryPotter extends SelectFactory
{
	// Конструктор.
	public function __construct()
	{
		$sel_name = 'harrypotter';
		$sel_options = [
			1 => 'Sorcerer\'s stone',
			'Chamber of secrets',
			'Prisoner of Azkaban',
			'Goblet of fire',
			'Order of Phoenix',
			'Half-blood Prince',
			'Deathly hallows'];
		parent::__construct(
			multiple_select: true, 
			multiple_return: true, 
			visible_size: 7, 
			name: $sel_name, 
			options: $sel_options);
	}
	
	/*
	// Конструктор.
	public function __construct()
	{
		$sel_name = 'harrypotter';
		$sel_options = [
			1 => 'Sorcerer\'s stone',
			'Chamber of secrets',
			'Prisoner of Azkaban',
			'Goblet of fire',
			'Order of Phoenix',
			'Half-blood Prince',
			'Deathly hallows'];
		parent::__construct(
			multiple_select: true, 
			autofocus: true, 
			require_select: true, 
			visible_size: 7, 
			is_disabled: false, 
			multiple_return: true, 
			name: $sel_name, options: $sel_options);
	}
	*/
}

// Пример наследования абстрактного класса фабрики контрола.
class InputStarWars extends InputFactory
{
	// Конструктор.
	public function __construct()
	{
		$name = 'starwars';
		parent::__construct($name);
	}
}

// Пример наследования абстрактного класса фабрики контрола.
class InputRadioButtonStarWars extends InputRadioFactory
{
	// Конструктор.
	public function __construct()
	{
		parent::__construct(new InputStarWars());
	}
}

// Пример наследования абстрактного класса фабрики контрола.
class InputRadioGroupStarWars extends InputRadioGroupFactory
{
	// Конструктор.
	public function __construct()
	{
		$rb_sw_film = new InputRadioButtonStarWars();
		$rb_sw_film = new CheckedInputRadioFactoryDecorator($rb_sw_film);
		//$rb_sw_film = new DisabledControlFactoryDecorator($rb_sw_film);
		$labels_values_dict = [
			'The Force Awakens' => 7,
			'The Last Jedi' => 8,
			'The Rise of Skywalker' => 9];
		parent::__construct(
			radio_factory: $rb_sw_film, 
			labels_values_dict: $labels_values_dict);
	}
}

// Пример класса для генерации форм. 
class FormGeneratorExample extends FormGenerator
{
	public function GenerateNew() : void
	{
		$formGet1 = new FormFactoryGET('work_test.php');
		
		$sel1 = new SelectFactoryExample();
		$select_name = 'languages';
		$sel1_options = ['Python'=>'Python', 'Go'=>'Go', 'PHP'=>'PHP'];
		$sel1_factory_options = [
			'name'=>$select_name,  
			'options'=>$sel1_options];
			
		$selHP = new SelectFactoryHarryPotter();
		$selHP = new AutofocusedControlFactoryDecorator($selHP);
		$selHP = new RequiredControlFactoryDecorator($selHP);
		
		$rbSW = new InputRadioGroupStarWars();
		
		$this->controls_generator->MakeFormBegin($formGet1, []);
		$this->controls_generator->MakeBrBr();
		$this->controls_generator->MakeControl($sel1, $sel1_factory_options);
		$this->controls_generator->MakeBrBr();
		$this->controls_generator->MakeControl($selHP, []);
		$this->controls_generator->MakeBrBr();
		$this->controls_generator->MakeControl($rbSW, []);
		$this->controls_generator->MakeBrBr();
		$this->controls_generator->MakeSubmitInput();
		$this->controls_generator->MakeFormEnd();
	}
}
?>