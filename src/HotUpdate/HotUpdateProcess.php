<?php
namespace Imi\HotUpdate;

use Imi\App;
use Imi\Util\Imi;
use Imi\Util\Coroutine;
use Imi\Bean\BeanFactory;
use Imi\Process\BaseProcess;
use Imi\Bean\Annotation\Bean;
use Imi\Process\Annotation\Process;

/**
 * @Bean("hotUpdate")
 * @Process("hotUpdate")
 */
class HotUpdateProcess extends BaseProcess
{
	/**
	 * 监视器类
	 * @var \Imi\HotUpdate\Monitor\BaseMonitor
	 */
	protected $monitorClass = \Imi\HotUpdate\Monitor\FileMTime::class;

	/**
	 * 每次检测时间间隔，单位：秒（有可能真实时间会大于设定的时间）
	 * @var integer
	 */
	protected $timespan = 1;

	/**
	 * 包含的路径
	 * @var array
	 */
	protected $includePaths = [];

	/**
	 * 排除的路径
	 * @var array
	 */
	protected $excludePaths = [];

	/**
	 * 默认监视路径
	 * @var array
	 */
	protected $defaultPath = [];

	/**
	 * 是否开启热更新，默认开启
	 * @var boolean
	 */
	protected $status = true;

	public function run(\Swoole\Process $process)
	{
		if(!$this->status)
		{
			return;
		}
		$this->defaultPath = [
			Imi::getNamespacePath(App::getNamespace()),
		];
		go(function(){
			$monitor = BeanFactory::newInstance($this->monitorClass, array_merge($this->defaultPath, $this->includePaths), $this->excludePaths);
			$reloadCmd = 'php ' . $_SERVER['argv'][0] . ' server/reload';
			$time = 0;
			while(true)
			{
				// 检测间隔延时
				sleep(min(max($this->timespan - (microtime(true) - $time), $this->timespan), $this->timespan));
				$time = microtime(true);
				// 检查文件是否有修改
				if($monitor->isChanged())
				{
					// 执行重新加载
					Coroutine::exec($reloadCmd);
				}
			}
		});
	}
}