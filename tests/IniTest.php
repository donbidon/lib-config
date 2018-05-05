<?php
/**
 * Ini class unit tests.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license   https://opensource.org/licenses/mit-license.php
 */

namespace donbidon\Lib\Config;

/**
 * Ini class unit tests.
 */
class IniTest extends \PHPUnit\Framework\TestCase
{
    /**
     * INI-string for testing
     *
     * @var string
     */
    protected static $iniString;

    /**
     * Tests parsing without sections.
     *
     * @return void
     * @covers \donbidon\Lib\Config\Ini::parse
     */
    public function testNoSections()
    {
        Ini::setSectionDelimiter('.');
        $iniString = <<< EOT
[section]
key1[key11]  = "value111"
key1.key12[] = 'value1120'
key1.key12[] = 'value1121'
key1.key12.3 = 'value1123'
EOT;
        $actual = Ini::parse($iniString, FALSE);
        $expected = [
            'key1' => [
                'key11' => 'value111',
                'key12' => [
                    0 => "value1120",
                    1 => "value1121",
                    3 => "value1123",
                ],
            ],
        ];
        self::assertEquals(
            $expected,
            $actual,
            "Testing of parsing without sections failed"
        );
    }

    /**
     * Tests parsing with sections.
     *
     * @return void
     * @covers \donbidon\Lib\Config\Ini::parse
     */
    public function testSections()
    {
        Ini::setSectionDelimiter('.');
        $iniString = <<< EOT
[section]
key1[key11]  = "value111"
key1.key12[] = 'value1120'
key1.key12[] = 'value1121'
key1.key12.3 = 'value1123'
EOT;
        $actual = Ini::parse($iniString, TRUE);
        $expected = [
            'section' => [
                'key1' => [
                    'key11' => 'value111',
                    'key12' => [
                        0 => "value1120",
                        1 => "value1121",
                        3 => "value1123",
                    ],
                ],
            ],
        ];
        self::assertEquals(
            $expected,
            $actual,
            "Testing of parsing with sections failed"
        );
    }

    /**
     * Tests parsing sections having delimiter into names.
     *
     * @return void
     * @covers \donbidon\Lib\Config\Ini::parse
     */
    public function testSectionsHavingDelimiter()
    {
        Ini::setSectionDelimiter('.');
        $iniString = <<< EOT
[section]
subsection.arg.foo = "source foo"
subsection.array[] = "value 0"
subsection.array[] = "value 1"

[section.subsection]
arg.foo = "overridden foo"
array.newKey = "new key"
EOT;
        $actual = Ini::parse($iniString, TRUE);
        $expected = [
            'section' => [
                'subsection' => [
                    'arg' => [
                        'foo' => "overridden foo",
                    ],
                    'array' => [
                        0        => "value 0",
                        1        => "value 1",
                        'newKey' => "new key",
                    ],
                ],
            ],
        ];
        self::assertEquals(
            $expected,
            $actual,
            "Testing of parsing with sections having delimiter into names failed"
        );
    }

    /**
     * Tests parsing using other section delimiter.
     *
     * @return void
     * @covers \donbidon\Lib\Config\Ini::setSectionDelimiter
     * @covers \donbidon\Lib\Config\Ini::parse
     */
    public function testSectionDelimiter()
    {
        Ini::setSectionDelimiter('_');
        $iniString = <<< EOT
[section]
key1[key11]  = "value111"
key1_key12[] = 'value1120'
key1_key12[] = 'value1121'
key1_key12_3 = 'value1123'
EOT;
        $actual = Ini::parse($iniString, FALSE);
        $expected = [
            'key1' => [
                'key11' => 'value111',
                'key12' => [
                    0 => "value1120",
                    1 => "value1121",
                    3 => "value1123",
                ],
            ],
        ];
        self::assertEquals(
            $expected,
            $actual,
            "Testing of parsing using other section delimiter failed"
        );
    }
}
