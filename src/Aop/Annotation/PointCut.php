<?php
namespace Imi\Aop\Annotation;

use Imi\Bean\Annotation\Base;
use Imi\Bean\Annotation\Parser;

/**
 * 切入点
 * @Annotation
 * @Target("METHOD")
 * @Parser("Imi\Aop\Parser\AopParser")
 */
class PointCut extends Base
{
    /**
     * 允许的切入点
     * @var array
     */
    public $allow = [];

    /**
     * 不允许的切入点，即使包含中有的，也可以被排除
     * @var array
     */
    public $deny = [];
}