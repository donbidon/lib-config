<?php
/**
 * Extended parse_ini_string() supporting nested sections.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

namespace donbidon\Lib\Config;

/**
 * Extended parse_ini_string() supporting nested sections.
 *
 * ```php
 * print_r(\donbidon\Lib\Config\Ini::parse(
 *     file_get_contents("/path/to/config.php"),
 *     TRUE)
 * );
 * ```
 * applied to the following ini-file
 * ```ini
 * ; <?php die; __halt_compiler();
 * [section]
 * subsection.arg.foo = "source foo"
 * subsection.array[] = "value 0"
 * subsection.array[] = "value 1"
 *
 * [section.subsection]
 * arg.foo = "overridden foo"
 * array.newKey = "new key"
 * ```
 * outputs
 * ```
 * Array
 * (
 *     [section] => Array
 *         (
 *             [subsection] => Array
 *                 (
 *                    [arg] => Array
 *                         (
 *                             [foo] => overridden foo
 *                         )
 *
 *                     [array] => Array
 *                         (
 *                             [0] => value 0
 *                             [1] => value 1
 *                             [newKey] => new key
 *                         )
 *              )
 *         )
 * )
 * ```
 * <!-- move: index.html -->
 * <a href="classes/donbidon.Lib.Config.Ini.html">\donbidon\Lib\Config\Ini</a>
 * -- extended parse_ini_string() supporting nested sections..
 * <!-- /move -->
 *
 * @link http://php.net/manual/en/function.parse-ini-string.php
 *       parse_ini_string()
 *
 * @static
 */
class Ini
{
    /**
     * Nested section delimiter
     *
     * @var string
     */
    protected static $delimiter = '.';

    /**
     * Sets nested sections delimiter
     *
     * @param  string $delimiter
     *
     * @return void
     */
    public static function setSectionDelimiter($delimiter)
    {
        self::$delimiter = $delimiter;
    }

    /**
     * Parses a configuration string.
     *
     * Cuts PHP-docblock containing file summary and ending with
     * "__halt_compiler();".
     *
     * @param string  $string
     * @param bool    $sections  Process sections
     * @param int     $mode      Scanner mode
     *
     * @return array|false
     *
     * @link http://php.net/manual/en/function.parse-ini-string.php
     *       parse_ini_string()
     */
    public static function parse(
        $string, $sections = false, $mode = INI_SCANNER_NORMAL
    )
    {
        $string = preg_replace(
            "/^\<\?php\s*\/\*\*.*__halt_compiler\(\)\;\s*(\?\>\s*)?/si",
            "",
            $string
        );
        $parsed = parse_ini_string($string, $sections, $mode);
        self::dig($parsed);

        return $parsed;
    }

    /**
     * Expands branches.
     *
     * @param  mixed $current  Current part of ini-array
     *
     * @return void
     *
     * @internal
     */
    protected static function dig(&$current)
    {
        if (is_array($current)) {
            $keys = array_keys($current);
            foreach ($keys as $key) {
                self::expandBranch($current, $key);
            }
        }
    }

    /**
     * Expands branch by key.
     *
     * @param  array  $current  Current part of ini-array
     * @param  string $key
     *
     * @return void
     *
     * @internal
     */
    protected static function expandBranch(array &$current, $key)
    {
        if (false === strpos($key, self::$delimiter)) {
            if (is_array($current[$key])) {
                self::dig($current[$key]);
            }
            // Otherwise we don't need to analyze and change structure.
        } else {
            $keys = explode(self::$delimiter, $key);
            $lastKey = array_pop($keys);
            $value = $current[$key];
            unset($current[$key]);
            /**
             * Next nested part of ini-array to expand
             *
             * @var mixed
             */
            $next = &$current;
            foreach ($keys as $subkey) {
                if (!isset($next[$subkey]) || !is_array($next[$subkey])) {
                    $next[$subkey] = [];
                }
                $next = &$next[$subkey];
            }
            $next[$lastKey] =
                is_array($value) &&
                isset($next[$lastKey]) &&
                is_array($next[$lastKey])
                    ? array_merge_recursive($next[$lastKey], $value)
                    : $value;
            self::dig($next[$lastKey]);
        }
    }
}
