<?php
namespace Imi\Server\TcpServer\Route;

use Imi\Bean\Annotation\Bean;
use Imi\Server\Route\RouteCallable;
use Imi\Server\Route\Annotation\Tcp\TcpRoute as TcpRouteAnnotation;
use Imi\Util\ObjectArrayHelper;

/**
 * @Bean("TcpRoute")
 */
class TcpRoute implements IRoute
{
    /**
     * 路由规则
     * url => Imi\Server\Route\Annotation\TcpServer\TcpRoute[]
     * @var array
     */
    protected $rules = [];

    /**
     * 路由解析处理
     * @param mixed $data
     * @return array
     */
    public function parse($data)
    {
        foreach($this->rules as $item)
        {
            if($this->checkCondition($data, $item['annotation']))
            {
                return [
                    'callable'      => $this->parseCallable([], $item['callable']),
                    'middlewares'   => $item['middlewares'] ?? [],
                ];
            }
        }
        return null;
    }

    /**
     * 增加路由规则，直接使用注解方式
     * @param Imi\Server\Route\Annotation\TcpServer\TcpRoute $annotation
     * @param mixed $callable
     * @param array $options
     * @return void
     */
    public function addRuleAnnotation(TcpRouteAnnotation $annotation, $callable, $options = [])
    {
        $this->rules[$this->hashKey($annotation)] = array_merge([
            'annotation'    => $annotation,
            'callable'      => $callable,
        ], $options);
    }

    /**
     * 清空路由规则
     * @return void
     */
    public function clearRules()
    {
        $this->rules = [];
    }

    /**
     * 路由规则是否存在
     * @param Imi\Server\Route\Annotation\TcpServer\TcpRoute $rule
     * @return boolean
     */
    public function existsRule(TcpRouteAnnotation $rule)
    {
        return isset($this->rules[$this->hashKey($rule)]);
    }

    /**
     * 获取路由规则
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * 对key做hash
     * @param mixed $key
     * @return boolean
     */
    private function hashKey($key)
    {
        return md5(serialize($key));
    }

    /**
     * 检查条件是否匹配
     * @param array|object $data
     * @param WSRouteAnnotation $annotation
     * @return boolean
     */
    private function checkCondition($data, TcpRouteAnnotation $annotation)
    {
        if([] === $annotation->condition)
        {
            return false;
        }
        foreach($annotation->condition as $name => $value)
        {
            if(ObjectArrayHelper::get($data, $name) !== $value)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 处理回调
     * @param array $params
     * @param mixed $callable
     * @return callable
     */
    private function parseCallable($params, $callable)
    {
        if($callable instanceof RouteCallable)
        {
            return $callable->getCallable($params);
        }
        else
        {
            return $callable;
        }
    }
}