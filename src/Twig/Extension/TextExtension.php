<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.07.
 * Time: 17:00
 */

namespace Wf\DockerWorkflowBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextExtension extends AbstractExtension
{
    /**
     * @return array|\Twig_Filter[]
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getFilters()
    {
        return [
            new TwigFilter('base64', 'base64_encode'),
            new TwigFilter('md_underline', [$this, 'underline']),
        ];
    }

    public function underline(string $title, string $lineChar = '='): string
    {
        return str_repeat($lineChar, mb_strlen($title));
    }
}
