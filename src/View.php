<?php

namespace Kykurniawan\Hmm;

use Kykurniawan\Hmm\Exceptions\ViewException;
use RuntimeException;

class View
{
    /**
     * Data that is made available to the Views.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Merge savedData and userData
     */
    protected $tempData = null;

    /**
     * The base directory to look in for our Views.
     *
     * @var string
     */
    protected $viewPath;

    /**
     * The render variables
     *
     * @var array
     */
    protected $renderVars = [];

    /**
     * Whether data should be saved between renders.
     *
     * @var boolean
     */
    protected $saveData = true;

    /**
     * Number of loaded views
     *
     * @var integer
     */
    protected $viewsCount = 0;

    /**
     * The name of the layout being used, if any.
     * Set by the `extend` method used within views.
     *
     * @var string|null
     */
    protected $layout;


    /**
     * Holds the sections and their data.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * The name of the current section being rendered,
     * if any.
     *
     * @var array<string>
     */
    protected $sectionStack = [];

    /**
     * Constructor
     */
    public function __construct(string $viewPath = null)
    {
        $this->viewPath = rtrim($viewPath, '\\/ ') . DIRECTORY_SEPARATOR;
    }

    /**
     * Builds the output based upon a file name and any
     * data that has already been set.
     *
     * Valid $options:
     *  - cache      Number of seconds to cache for
     *  - cache_name Name to use for cache
     *
     * @param string       $view     File name of the view source
     * @param array|null   $options  Reserved for 3rd-party uses since
     *                               it might be needed to pass additional info
     *                               to other template engines.
     * @param boolean|null $saveData If true, saves data for subsequent calls,
     *                               if false, cleans the data after displaying,
     *                               if null, uses the config setting.
     *
     * @return string
     */
    public function render(string $view, array $options = null, bool $saveData = null): string
    {
        // Store the results here so even if
        // multiple views are called in a view, it won't
        // clean it unless we mean it to.
        $saveData = $saveData ?? $this->saveData;
        $fileExt = pathinfo($view, PATHINFO_EXTENSION);
        $realPath = empty($fileExt) ? $view . '.php' : $view; // allow Views as .html, .tpl, etc.
        $this->renderVars['view'] = $realPath;
        $this->renderVars['options'] = $options ?? [];
        $this->renderVars['file'] = $this->viewPath . $this->renderVars['view'];

        if (!is_file($this->renderVars['file'])) {
            $this->renderVars['file'] = realpath($this->renderVars['file']);
        }

        // locateFile will return an empty string if the file cannot be found.
        if (empty($this->renderVars['file'])) {
            throw ViewException::forInvalidFile($this->renderVars['view']);
        }

        // Make our view data available to the view.
        $this->tempData = $this->tempData ?? $this->data;

        if ($saveData) {
            $this->data = $this->tempData;
        }

        // Save current vars
        $renderVars = $this->renderVars;

        $output = (function (): string {
            extract($this->tempData);
            ob_start();
            include $this->renderVars['file'];
            return ob_get_clean() ?: '';
        })();

        // Get back current vars
        $this->renderVars = $renderVars;

        // When using layouts, the data has already been stored
        // in $this->sections, and no other valid output
        // is allowed in $output so we'll overwrite it.
        if (!is_null($this->layout) && $this->sectionStack === []) {
            $layoutView   = $this->layout;
            $this->layout = null;
            // Save current vars
            $renderVars = $this->renderVars;
            $output     = $this->render($layoutView, $options, $saveData);
            // Get back current vars
            $this->renderVars = $renderVars;
        }

        $this->tempData = null;

        return $output;
    }

    /**
     * Builds the output based upon a string and any
     * data that has already been set.
     * Cache does not apply, because there is no "key".
     *
     * @param string       $view     The view contents
     * @param array|null   $options  Reserved for 3rd-party uses since
     *                               it might be needed to pass additional info
     *                               to other template engines.
     * @param boolean|null $saveData If true, saves data for subsequent calls,
     *                               if false, cleans the data after displaying,
     *                               if null, uses the config setting.
     *
     * @return string
     */
    public function renderString(string $view, array $options = null, bool $saveData = null): string
    {
        $saveData = $saveData ?? $this->saveData;
        $this->tempData = $this->tempData ?? $this->data;

        if ($saveData) {
            $this->data = $this->tempData;
        }

        $output = (function (string $view): string {
            extract($this->tempData);
            ob_start();
            eval('?>' . $view);
            return ob_get_clean() ?: '';
        })($view);
        $this->tempData = null;

        return $output;
    }

    /**
     * Extract first bit of a long string and add ellipsis
     *
     * @param  string  $string
     * @param  integer $length
     * @return string
     */
    public function excerpt(string $string, int $length = 20): string
    {
        return (strlen($string) > $length) ? substr($string, 0, $length - 3) . '...' : $string;
    }

    /**
     * Sets several pieces of view data at once.
     *
     * @param array  $data
     * @param string $context The context to escape it for: html, css, js, url
     *                        If null, no escaping will happen
     */
    public function setData(array $data = [], string $context = null): View
    {
        if ($context) {
            $data = $this->esc($data, $context);
        }

        $this->tempData = $this->tempData ?? $this->data;
        $this->tempData = array_merge($this->tempData, $data);

        return $this;
    }

    /**
     * Sets a single piece of view data.
     *
     * @param string $name
     * @param mixed  $value
     * @param string $context The context to escape it for: html, css, js, url
     *                        If null, no escaping will happen
     *
     */
    public function setVar(string $name, $value = null, string $context = null): View
    {
        if ($context) {
            $value = $this->esc($value, $context);
        }

        $this->tempData        = $this->tempData ?? $this->data;
        $this->tempData[$name] = $value;

        return $this;
    }

    /**
     * Removes all of the view data from the system.
     *
     * @return RendererInterface
     */
    public function resetData(): View
    {
        $this->data = [];

        return $this;
    }

    /**
     * Returns the current data that will be displayed in the view.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->tempData ?? $this->data;
    }

    /**
     * Specifies that the current view should extend an existing layout.
     *
     * @param string $layout
     *
     * @return void
     */
    public function extend(string $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Starts holds content for a section within the layout.
     *
     * @param string $name Section name
     *
     * @return void
     *
     */
    public function section(string $name)
    {
        //Saved to prevent BC.
        $this->sectionStack[] = $name;

        ob_start();
    }

    /**
     * Captures the last section
     *
     * @return void
     * @throws RuntimeException
     */
    public function endSection()
    {
        $contents = ob_get_clean();

        if ($this->sectionStack === []) {
            throw new RuntimeException('View themes, no current section.');
        }

        $section = array_pop($this->sectionStack);

        // Ensure an array exists so we can store multiple entries for this.
        if (!array_key_exists($section, $this->sections)) {
            $this->sections[$section] = [];
        }

        $this->sections[$section][] = $contents;
    }

    /**
     * Renders a section's contents.
     *
     * @param string $sectionName
     */
    public function renderSection(string $sectionName)
    {
        if (!isset($this->sections[$sectionName])) {
            echo '';

            return;
        }

        foreach ($this->sections[$sectionName] as $key => $contents) {
            echo $contents;
            unset($this->sections[$sectionName][$key]);
        }
    }

    /**
     * Used within layout views to include additional views.
     *
     * @param string     $view
     * @param array|null $options
     * @param boolean    $saveData
     *
     * @return string
     */
    public function include(string $view, array $options = null, $saveData = true): string
    {
        return $this->render($view, $options, $saveData);
    }

    protected function esc($data, string $context = 'html', string $encoding = null)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = $this->esc($value, $context);
            }
        }

        if (is_string($data)) {
            $context = strtolower($context);

            // Provide a way to NOT escape data since
            // this could be called automatically by
            // the View library.
            if (empty($context) || $context === 'raw') {
                return $data;
            }

            if (!in_array($context, ['html', 'js', 'css', 'url', 'attr'], true)) {
                throw ViewException::forInvalidArgumentException('Invalid escape context provided.');
            }

            $method = $context === 'attr' ? 'escapeHtmlAttr' : 'escape' . ucfirst($context);

            static $escaper;
            if (!$escaper) {
                $escaper = new Escaper($encoding);
            }

            if ($encoding && $escaper->getEncoding() !== $encoding) {
                $escaper = new Escaper($encoding);
            }

            $data = $escaper->$method($data);
        }

        return $data;
    }
}
