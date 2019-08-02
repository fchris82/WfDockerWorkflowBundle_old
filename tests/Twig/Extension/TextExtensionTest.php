<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 16:55
 */

namespace Wf\DockerWorkflowBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Wf\DockerWorkflowBundle\Twig\Extension\TextExtension;

class TextExtensionTest extends TestCase
{
    /**
     * @param string      $text
     * @param string      $result
     * @param string|null $lineChar
     *
     * @dataProvider getUnderlines
     */
    public function testUnderline($text, $result, $lineChar = null)
    {
        $extension = new TextExtension();
        $response = null === $lineChar
            ? $extension->underline($text)
            : $extension->underline($text, $lineChar)
        ;
        $this->assertEquals($result, $response);
    }

    public function getUnderlines(): array
    {
        return [
            ['', ''],
            ['Test', '===='],
            ['Test', '----', '-'],
            ['Árvíztűrőtükörfúrógép!', '======================'],
        ];
    }
}
