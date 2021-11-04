<?php
namespace Ohms\Interview;

use Exception;
use Ohms\Transcript;

/**
 * Abstract Model for the XML Cache File
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
abstract class AbstractInterview
{
    /**
     * @var ?AbstractInterview
     */
    protected static ?AbstractInterview $Instance = null;

    /**
     * @var ?Transcript
     */
    public ?Transcript $Transcript;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @var int
     */
    protected int $index = 0;

    /**
     * @param array       $viewerConfig
     * @param ?string     $tmpDir
     * @param string|null $cacheFile
     * @throws Exception
     */
    abstract protected function __construct(array $viewerConfig, ?string $tmpDir, ?string $cacheFile);

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
        // empty
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property '.$name.
                ' in '.$trace[0]['file'].
                ' on line '.$trace[0]['line'],
                E_USER_NOTICE
            );
            return null;
        }
    }

    /**
     * @return bool
     */
    public function hasIndex(): bool
    {
        return strlen($this->index) > 0;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get the current instance and instantiate if needed.
     *
     * @param array       $viewerConfig
     * @param string      $tmpDir
     * @param string|null $cacheFile
     * @return AbstractInterview
     */
    public static function getInstance(array $viewerConfig, string $tmpDir, ?string $cacheFile = null): AbstractInterview
    {
        if (!self::$Instance) {
            $class          = static::class;
            self::$Instance = new $class($viewerConfig, $tmpDir, $cacheFile);
        }
        return self::$Instance;
    }

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->data);
    }
}
