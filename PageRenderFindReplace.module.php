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
			'title' => 'Page Render Find/Replace',
			'version' => '0.0.2',
			'summary' => 'Apply find/replace patterns to rendered page content.',
			'requires' => 'PHP>=7.1, ProcessWire>=3.0.164, TextformatterFindReplace',
			'autoload' => true,
		];
	}

	public function init() {
		$this->addHookAfter('Page::render', $this, 'applyReplacements');
	}

	protected function applyReplacements(HookEvent $event) {
		if (empty($event->return)) return;
		if (!$this->isPageEnabled($event->object)) return;
		$formatter = $this->modules->get('TextformatterFindReplace');
		$return = $event->return;
		$formatter->format($return);
		if (!isset(static::$loggedPages[$event->object->id]) && $return != $event->return && $this->isLoggingEnabled()) {
			static::$loggedPages[$event->object->id] = true;
			$this->addLogRow($event->object);
		}
		if (!$this->log_only) {
			$event->return = $return;
		}
	}

	protected function ___isPageEnabled(Page $page): bool {
		if (!$this->apply_to_pages) return false;
		if ($this->apply_to_pages == 'all') return true;
		if ($this->apply_to_pages == 'non_admin') return $page->template != 'admin';
		if ($this->apply_to_pages == 'selector') return $page->matches($this->pages_selector);
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

		/** @var InputfieldFieldset */
		$apply_to = $this->modules->get('InputfieldFieldset');
		$apply_to->label = $this->_('Apply to');
		$apply_to->icon = 'filter';
		$inputfields->add($apply_to);

		/** @var InputfieldRadios */
		$apply_to_pages = $this->modules->get('InputfieldRadios');
		$apply_to_pages->name = 'apply_to_pages';
		$apply_to_pages->label = $this->_('Apply to pages');
		$apply_to_pages->addOptions([
			'all' => $this->_('All pages'),
			'non_admin' => $this->_('All non-admin pages'),
			'selector' => $this->_('Pages matching a selector'),
		]);
		$apply_to_pages->value = $this->apply_to_pages;
		$apply_to->add($apply_to_pages);

		/** @var InputfieldSelector */
		$apply_to_pages_selector = $this->modules->get('InputfieldSelector');
		$apply_to_pages_selector->name = 'apply_to_pages_selector';
		$apply_to_pages_selector->label = $this->_('Pages matching selector');
		$apply_to_pages_selector->description = $this->_('This option defines which pages replacements should occur for.');
		$apply_to_pages_selector->value = $this->apply_to_pages_selector;
		$apply_to_pages_selector->showIf = 'apply_to_pages=selector';
		$apply_to->add($apply_to_pages_selector);

		/** @var InputfieldFieldset */
		$logging = $this->modules->get('InputfieldFieldset');
		$logging->label = $this->_('Logging');
		$logging->icon = 'database';
		$inputfields->add($logging);

		/** @var InputfieldSelect */
		$enable_logging = $this->modules->get('InputfieldSelect');
		$enable_logging->name = 'enable_logging';
		$enable_logging->label = $this->_('Enable logging');
		$enable_logging->description = $this->_('If you want to log replacements as they happen, choose applicable setting here.');
		$enable_logging->notes = $this->_('Please note that enabling logging for **everyone** may generate a lot of log data, so it may be a bad idea *especially* on a busy site.');
		$enable_logging->addOptions([
			null => $this->_('Disabled'),
			'superusers' => $this->_('Enabled for superusers'),
			'authenticated' => $this->_('Enabled for authenticated users'),
			'everyone' => $this->_('Enabled for everyone'),
		]);
		$enable_logging->value = $this->enable_logging;
		$logging->add($enable_logging);

		/** @var InputfieldCheckbox */
		$log_only = $this->modules->get('InputfieldCheckbox');
		$log_only->name = 'log_only';
		$log_only->label = $this->_('Log only');
		$log_only->description = $this->_('With this option enabled the module will log changes without actually altering page content.');
		$log_only->checked = (bool) $this->log_only;
		$logging->add($log_only);
	}

}
