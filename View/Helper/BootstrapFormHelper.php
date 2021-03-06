<?php
App::uses('AppHelper', 'View/Helper');
App::uses('Set', 'Utility');

class BootstrapFormHelper extends AppHelper {

	public $helpers = array('Html', 'Form');

	public function input($name, $options = array()) {
		$default = array(
			'type' => null,
			'label' => null,
			'prepend' => null, // to convert .input-prepend
			'after' => null, // to convert .help-inline
			'div' => array(
				'class' => 'input',
			),
		);
		$options = Set::merge($default, $options);
		$options['type']='text'; //To disable spin box

		$out = array();

		$label = $options['label'];
		if ($label !== false) {
			$out[] = $this->Form->label($name, $label);
		}

		$options['label'] = false;
		$divOptions = $options['div'];
		$options['div'] = false;
		$options['legend'] = false;
		$options['error'] = array(
			'attributes' => array(
				'wrap' => 'span',
				'class' => 'help-inline',
			),
		);
		if ($options['after']) {
			$options['after'] = $this->Html->tag('span', $options['after'], array(
				'class' => 'help-block',
			));
		}
		if ($options['prepend']) {
			$options = $this->_prepend($options['prepend'], $options);
			$options['before'] = $options['prepend'];
		}

		$form = $this->Form->input($name, $options);
		if (!empty($options['multiple']) && $options['multiple'] === 'checkbox') {
			$form = $options['after'] .$this->_multipleCheckbox($form, $options);
		}
		elseif ($options['type'] === 'checkbox') {
			$form = $this->_checkbox($name, $options);
		}
		elseif ($options['type'] === 'radio') {
			$form = $this->_radio($form, $options);
		}
		$out[] = $this->Html->div($divOptions['class'], $form, $divOptions);

		$errorClass = '';
		if ($this->Form->error($name)) {
			$errorClass = ' error';
		}
		return $this->Html->div('clearfix' . $errorClass, implode("\n", $out));
	}

	protected function _prepend($before, $options) {
		if(strpos($options['prepend'], 'checked="checked"')){
			$before = $this->Html->tag('span', $before, array('class' => 'add-on active'));
		}
		else {
			$before = $this->Html->tag('span', $before, array('class' => 'add-on'));
		}
		$options['prepend'] = '<div class="input-prepend">' . $before;		
		$options['after'] .= '</div>';
		return $options;
	}

	protected function _checkbox($name, $options) {
		$default = array(
			'ul' => array('class' => 'inputs-list'),
			'li' => array(),
		);

		$form = $this->Form->input($name, $options);

		$options = Set::merge($default, $options);
		$form = $this->Html->tag('label', $form);
		$form = $this->Html->tag('li', $form, $options['li']);
		$form = $this->Html->tag('ul', $form, $options['ul']);
		return $form;
	}

	protected function _radio($out, $options) {
		$default = array(
			'ul' => array('class' => 'inputs-list'),
			'li' => array(),
		);
		$options = Set::merge($default, $options);

		if (!preg_match_all('/(<input type="radio"[^>]+>)(((?!<input).)*)/m', $out, $matches)) {
			return $out;
		}

		$hidden = '';
		if (preg_match('/<input type="hidden"[^>]+>/m', $out, $match)) {
			$hidden = $match[0];
		}

		$error = '';
		if (preg_match('/<span class="' . $options['error']['attributes']['class'] . '"[^>]*>[^<]*<\/span>/m', $out, $match)) {
			$error = '<p>' . $match[0] . '</p>';
		}

		$lines = array();
		foreach ($matches[0] as $key => $value) {
			$line = $matches[1][$key] . '&nbsp;' . $this->Html->tag('span', $matches[2][$key]);
			$line = $this->Html->tag('label', $line);
			$lines[] = $this->Html->tag('li', $line, $options['li']);
		}

		$out = $hidden;
		$out .= $this->Html->div('clearfix', $this->Html->tag('ul', implode("\n", $lines), $options['ul']));
		$out .= $error;
		return $out;
	}

	protected function _multipleCheckbox($out, $options) {
		$default = array(
			'ul' => array('class' => 'inputs-list'),
			'li' => array(),
		);
		$options = Set::merge($default, $options);

		if (!preg_match_all('/<div[^>]+>(<input type="checkbox"[^>]+>)(<label[^>]+>)([^<]*)(<\/label>)<\/div>/m', $out, $matches)) {
			return $out;
		}

		if (!preg_match('/<input type="hidden"[^>]+>/m', $out, $match)) {
			return $out;
		}
		$hidden = $match[0];

		$error = '';
		if (preg_match('/<span class="' . $options['error']['attributes']['class'] . '"[^>]*>[^<]*<\/span>/m', $out, $match)) {
			$error = '<p>' . $match[0] . '</p>';
		}

		$lines = array();
		foreach ($matches[0] as $key => $value) {
			$line = $matches[2][$key] . $matches[1][$key] . '&nbsp;';
			$line .= $this->Html->tag('span', $matches[3][$key]) . $matches[4][$key];
			$lines[] = $this->Html->tag('li', $line, $options['li']);
		}

		$out = $hidden;
		$out .= $this->Html->div('clearfix', $this->Html->tag('ul', implode("\n", $lines), $options['ul']));
		$out .= $error;
		return $out;
	}

	public function submit($caption = null, $options = array()) {
		$default = array(
			'type' => 'submit',
			'div' => array('class' => 'actions'),
			'class' => 'btn primary',
			'data-loading-text' => __d('TwitterBootstrap', 'Submiting...'),
		);
		$options += $default;
		$divOptions = $options['div'];
		unset($options['div']);

		$out = $this->Html->tag('button', $caption, $options);
		$out = $this->Html->tag('div', $out, $divOptions);
		return $out;
	}
	
	public function select($name, $options = array(), $attributes = array()) {		
		$out = $this->Form->select($name, $options,$attributes);
		$out = $this->Html->div('input',$out);
		$out = '<label>'.$attributes['label'].'</label>'.$out;
		$out = $this->Html->div('clearfix',$out);
		return $out;
		
	}

}