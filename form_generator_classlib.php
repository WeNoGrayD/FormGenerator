<?php

/*
	Пакет, предоставляющий интерфейсы и классы для генерации форм.
 */

// Абстрактный класс для генерирования форм.
abstract class FormGenerator
{
	protected ControlsGenerator $controls_generator;
	
	// Конструктор.
	public function __construct()
	{
		$this->controls_generator = new ControlsGenerator();
	}
	
	// Абстрактный метод генерации формы.
	public abstract function GenerateNew() : void;
}

// Фабрика форм.
abstract class FormFactory
{
	// Страница, на которую перенаправляется форма.
	public string $form_action;
	
	// HTTP-метод формы.
	public string $form_method;
	
	// Конструктор.
	public function __construct(string $form_action, string $form_method)
	{
		$this->form_action = $form_action;
		$this->form_method = $form_method;
	}
	
	// Создание открывающего тега формы.
	public function MakeFormBegin(
		$form_action = null, 
		$form_method = null) : string
	{
		$form_action = $form_action ?? $this->form_action;
		$form_method = $form_method ?? $this->form_method;
		return "<form action='$form_action' method='$form_method'>\n";
	}
	
	// Создание закрывающего тега формы.
	public static function MakeFormEnd() : string
	{
		return "</form>";
	}
}

// Интерфейс фабрики контролов.
interface IControlFactory
{
	// Хотелось бы сделать интерфейс не совсем бесполезным, но в пхп интерфейсы не поддерживают свойства.
	public function Product(array $params);
}

// Интерфейс декоратора фабрики контролов.
abstract class ControlFactoryDecorator implements IControlFactory
{
	// Фабрика контрола, поверх которой работает декоратор.
	public IControlFactory $control_factory;
	
	// Конструктор.
	public function __construct(IControlFactory $control_factory)
	{
		$this->control_factory = $control_factory;
	}

	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = $this->control_factory->Product($params);
		return $control;
	}
	
	// Метод добавления атрибута в тег контрола.
	public function AddAttribute(string $control, string $attr) : string
	{
		// Поиск первого вхождения закрывающей угловой скобки в теге контрола,
		// т.е. места, в которое можно добавить новый атрибут.
		$extend_attr_pos = strpos($control, '>');
		// Строка открывающего тега.
		$control_open_tag = substr($control, 0, $extend_attr_pos); 
		// строка закрывающего тега.
		$control_close_tag = substr($control, $extend_attr_pos);
		// Итоговая строка.
		$control = $control_open_tag . $attr . ' ' . $control_close_tag;
		
		return $control;
	}
}

// Класс декоратора фабрики контрола, который производит контрол, на котором держится автоматический фокус.
class AutofocusedControlFactoryDecorator extends ControlFactoryDecorator
{
	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = parent::Product($params);
		$control = parent::AddAttribute($control, 'autofocus');
		return $control;
	}
}

// Класс декоратора фабрики контрола, который производит отключённый контрол.
class DisabledControlFactoryDecorator extends ControlFactoryDecorator
{
	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = parent::Product($params);
		$control = parent::AddAttribute($control, 'disabled');
		return $control;
	}
}

// Класс декоратора фабрики контрола, который производит контрол, в котором требуется выполнить дейстиве.
class RequiredControlFactoryDecorator extends ControlFactoryDecorator
{
	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = parent::Product($params);
		$control = parent::AddAttribute($control, 'required');
		return $control;
	}
}

// Абстрактная фабрика контрола раскрывающегося списка.
abstract class SelectFactory implements IControlFactory
{
	// Может ли пользователь выбрать несколько значений в списке.
	protected bool $multiple_select;
	
	// Возвращает ли этот раскрывающийся список массив выбранных значений 
	// (если multiple select == true) или только первое из массива/единственное выбранное значение.
	protected bool $multiple_return;
	
	// Количество видных параметров в раскрывающемся списке. 
	protected int $visible_size;

	// Имя переменной, которую представляет этот контрол.
	protected string $name; 
	
	// Элементы в раскрывающемся списке.
	protected Iterable $options;
	
	// Конструктор.
	public function __construct(
		bool $multiple_select = false, 
		int $visible_size = 1,
		bool $multiple_return = false,
		string $name = '',
		Iterable $options = [])
	{
		$this->multiple_select = $multiple_select;
		$this->visible_size = $visible_size;
		$this->multiple_return = $multiple_return;
		$this->name = $name;
		$this->options = $options;
	}
	
	// Фабричный метод.
	public function MakeSelect(
		?bool $multiple_select = null, 
		?int $visible_size = null,
		?bool $multiple_return = null,
		?string $name = null,
		?Iterable $options = null) : string
	{
		$multiple_select = $multiple_select ?? $this->multiple_select;
		$multiple_return = $multiple_return ?? $this->multiple_return;
		$visible_size = $visible_size ?? $this->visible_size;
		$name = $name ?? $this->name;
		$options = $options ?? $this->options;
		
		$select_str = "<select ";
		if ($multiple_select) $select_str .= 'multiple ';
		$select_str .= 'size="' . (string)$visible_size . '" ';
		$select_str .= 'name="' . $name . ($multiple_return ? '[]' : '') . '" ';
		$select_str .= ">";
		foreach ($options as $option_name=>$option_value)
		{
			$select_str .= "\n\t<option value=\"$option_name\">$option_value</option>";
		}
		$select_str .= "\n</select><br>\n";
		
		return $select_str;
	}
	
	// Псевдоним для фабричного метода.
	public function Product(array $params) : string
	{
		$control = $this->MakeSelect(...$params);
		return $control;
	}
	
	/*
	// Может ли пользователь выбрать несколько значений в списке.
	protected bool $multiple_select;
	
	// Автофокус на элементе управления.
	protected bool $autofocus;
	
	// Обязателен ли выбор элемента из раскрывающегося списка.
	protected bool $require_select;
	
	// Количество видных параметров в раскрывающемся списке. 
	protected int $visible_size;
	
	// Должен ли раскрывающийся список быть отключён по умолчанию.
	protected bool $is_disabled;
	
	// Имя переменной, которую представляет этот контрол.
	protected string $name; 
	
	// Возвращает ли этот раскрывающийся список массив выбранных значений 
	// (если multiple select == true) или только первое из массива/единственное выбранное значение.
	protected bool $multiple_return;
	
	// Элементы в раскрывающемся списке.
	protected Iterable $options;
	
	// Конструктор.
	public function __construct(
		bool $multiple_select = false, 
		bool $autofocus = false, 
		bool $require_select = false, 
		int $visible_size = 1,
		bool $is_disabled = false,
		bool $multiple_return = false,
		string $name = '',
		Iterable $options = [])
	{
		$this->multiple_select = $multiple_select;
		$this->autofocus = $autofocus;
		$this->require_select = $require_select;
		$this->visible_size = $visible_size;
		$this->is_disabled = $is_disabled;
		$this->multiple_return = $multiple_return;
		$this->name = $name;
		$this->options = $options;
	}
	
	// Фабричный метод.
	public function MakeSelect(
		?bool $multiple_select = null, 
		?bool $autofocus = null, 
		?bool $require_select = null, 
		?int $visible_size = null,
		?bool $is_disabled = null,
		?bool $multiple_return = null,
		?string $name = null,
		?Iterable $options = null) : string
	{
		$multiple_select = $multiple_select ?? $this->multiple_select;
		$autofocus = $autofocus ?? $this->autofocus;
		$require_select = $require_select ?? $this->require_select;
		$visible_size = $visible_size ?? $this->visible_size;
		$is_disabled = $is_disabled ?? $this->is_disabled;
		$multiple_return = $multiple_return ?? $this->multiple_return;
		$name = $name ?? $this->name;
		$options = $options ?? $this->options;
		
		$select_str = "<select ";
		if ($multiple_select) $select_str .= 'multiple ';
		if ($autofocus) $select_str .= 'autofocus ';
		if ($require_select) $select_str .= 'required ';
		$select_str .= 'size="' . (string)$visible_size . '" ';
		if ($is_disabled) $select_str .= 'disabled ';
		$select_str .= 'name="' . $name . ($multiple_return ? '[]' : '') . '" ';
		$select_str .= ">";
		foreach ($options as $option_name=>$option_value)
		{
			$select_str .= "\n\t<option value=\"$option_name\">$option_value</option>";
		}
		$select_str .= "\n</select><br>\n";
		
		return $select_str;
	}
	*/
}

// Абстрактная фабрика контрола ввода.
abstract class InputFactory implements IControlFactory
{
	// Имя переменной, которую представляет этот контрол.
	protected string $name; 
	
	// Конструктор.
	public function __construct(string $name = '')
	{
		$this->name = $name;
	}
	
	// Фабричный метод.
	public function MakeInput(?string $name = null) : string
	{
		$name = $name ?? $this->name;
		
		$select_str = "<input ";
		$select_str .= "name='$name' ";
		$select_str .= ">";
		$select_str .= "\n</input>";
		
		return $select_str;
	}
	
	// Псевдоним для фабричного метода.
	public function Product(array $params) : string
	{
		$control = $this->MakeInput(...$params);
		return $control;
	}
}

// Абстрактная фабрика контрола радиокнопки.
abstract class InputRadioFactory extends ControlFactoryDecorator
{
	// Значение, которое этот контрол передаёт на сервер.
	protected string $value; 
	
	// Конструктор.
	public function __construct(
		InputFactory $input_factory,
		string $value = '0')
	{
		parent::__construct($input_factory);
		$this->value = $value;
	}
	
	// Фабричный метод.
	public function Product(array $params) : string
	{
		// На этом этапе проводится обработка наличия параметра значения
		// внутри переданного массива параметров.
		if (isset($params['value']))
		{
			$value = $params['value'];
			// Удаление лишнего параметра. Дальше радиокнопки он пойти не должен.
			unset($params['value']);
		}
		else 
			$value = $this->value;
		
		$control = parent::Product($params);
		$control = parent::AddAttribute($control, 'type=radio');
		$control = parent::AddAttribute($control, "value='$value' ");
		
		return $control;
	}
}

// Класс декоратора фабрики радиокнопок, который производит отмеченные радиокнопки.
class CheckedInputRadioFactoryDecorator extends ControlFactoryDecorator
{
	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = parent::Product($params);
		$control = parent::AddAttribute($control, 'checked');
		return $control;
	}
}

// Абстрактная фабрика группы радиокнопок (вместе с произвольными контролами справа от кнопок).
abstract class InputRadioGroupFactory implements IControlFactory
{
	// Шаблон радиокнопки.
	protected IControlFactory $radio_factory; 
	
	// Перечисление обозначений (видных пользователю) и значений (отправляемых на сервер) радиокнопок.
	protected Iterable $labels_values_dict; 
	
	// Конструктор.
	public function __construct(
		IControlFactory $radio_factory,
		Iterable $labels_values_dict = [])
	{
		$this->radio_factory = $radio_factory;
		$this->labels_values_dict = $labels_values_dict;
	}
	
	// Псевдоним для фабричного метода.
	public function MakeRadioGroup(array $params)
	{
		$radiogroup = "";
		foreach ($this->labels_values_dict as $label => $value)
		{
			$radiogroup .= $this->radio_factory->Product(['value' => $value]);
			$radiogroup .= $label;
			$radiogroup .= "<br>\n";
		}
		
		return $radiogroup;
	}
	
	// Фабричный метод.
	public function Product(array $params) : string
	{
		$control = $this->MakeRadioGroup($params);
		return $control;
	}
}

// Класс статических методов генерации контролов на форме.
class ControlsGenerator
{
	// Конструктор.
	public function __construct()
	{ }
	
	// Создание открывающего тега формы.
	public function MakeFormBegin(FormFactory $form, array $form_instance_options) : void
	{
		echo $form->MakeFormBegin(...$form_instance_options);
	}
	
	// Создание закрывающего тега формы.
	public function MakeFormEnd() : void
	{
		echo FormFactory::MakeFormEnd();
	}
	
	// Создание контрола.
	public function MakeControl(IControlFactory $control_factory, array $control_instance_options) : void
	{
		echo $control_factory->Product($control_instance_options);
	}
	
	// Создание кнопки подтверждения без добавления класса.
	public function MakeSubmitInput() : void
	{
		echo "<input type=submit><br>\n";
	}
	
	// Создание двух абзацев.
	public function MakeBrBr() : void
	{
		echo "<br><br>\n";
	}
}

?>