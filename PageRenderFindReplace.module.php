<?php namespace ProcessWire;

/**
 * Page Render Find/Replace
 *
 * This module applies replacements specified via TextformatterFindReplace to rendered page content.
 *
 * @license Mozilla Public License v2.0 http://mozilla.org/MPL/2.0/
 */
class PageRenderFindReplace extends WireData implements Module, ConfigurableModule {

	/**
	 * Keeps track of pages for which a log row has been written during current request
	 *
	 * @var array
	 */
	static protected $loggedPages = [];

	public static function getModuleInfo() {
		return [
			'title' => 'Page Render Find/replace',
			'version' => '0.0.1',
			'summary' => 'Apply find/replace patterns to rendered page content.',
			'requires' => 'PHP>=7.1, ProcessWire>=3.0.164, TextformatterFindReplace',
			'autoload' => 'template!=admin',
		];
	}

	public function init() {
		$this->addHookAfter('Page::render', $this, 'applyReplacements');
	}

	protected function applyReplacements(HookEvent $event) {
		if (empty($event->return)) return;
		$formatter = $this->modules->get('TextformatterFindReplace');
		$return = $event->return;
		$formatter->format($return);
		if (!isset(static::$loggedPages[$event->object->id]) && $return != $event->return && $this->isLoggingEnabled()) {
			static::$loggedPages[$event->object->id] = true;
			$this->addLogRow($event->object);
		}
		$event->return = $return;
	}

	protected function ___isLoggingEnabled(): bool {
		if ($this->enable_logging == null) return false;
		if ($this->enable_logging == 'everyone') return true;
		if ($this->enable_logging == 'superusers' && $this->user->isSuperuser()) return true;
		if ($this->enable_logging == 'authenticated' && $this->user->isLoggedin()) return true;
	}

	protected function ___addLogRow(Page $page) {
		$this->log->save('page-render-find-replace', $page->url);
	}

	public function getModuleConfigInputfields(InputfieldWrapper $inputfields) {

		/** @var InputfieldSelect */
		$field = $this->modules->get('InputfieldSelect');
		$field->name = 'enable_logging';
		$field->label = $this->_('Enable logging?');
		$field->description = $this->_('If you want to log replacements as they happen, enable applicable setting here.');
		$field->notes = $this->_('Please note that enabling logging for **everyone** may generate a lot of log data, so it may be a bad idea *especially* on a busy site.');
		$field->addOptions([
			null => $this->_('Disabled'),
			'superusers' => $this->_('Enabled for superusers'),
			'authenticated' => $this->_('Enabled for authenticated users'),
			'everyone' => $this->_('Enabled for everyone'),
		]);
		$field->value = $this->enable_logging;
		$inputfields->add($field);
	}

}
