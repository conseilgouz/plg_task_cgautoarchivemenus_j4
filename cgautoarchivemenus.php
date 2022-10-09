<?php
/** CG Auto Archive MENUS
*
* Version			: 1.1.0
* Package			: Joomla 4.1
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
/* ----- Pour Mairie de Santeuil 
   Les fichiers "menu de cantine" sont stockés dans le répertoire images\Menus_Cantine.
   les noms des fichiers contenant les menus de cantine doivent être 
   <année>_Sem_<no de semaine>.pdf
   Par exemple 2021_Sem_01.pdf
   Ce plugin va déplacer les fichiers dont la semaine est dépassé dans le répertoire
   images\Menus_Cantine\archives
*/
/* Version 1.1.0 : changement des noms de fichiers
   <année><no de mois>_-_<mois en alpha>.pdf
*/

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;

class PlgTaskCGAutoArchiveMenus extends CMSPlugin implements SubscriberInterface
{
		use TaskPluginTrait;


	/**
	 * @var boolean
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * @var string[]
	 *
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'archivemenus' => [
			'langConstPrefix' => 'PLG_TASK_CGAUTOARCHIVEMENUS',
			'method'          => 'autoarchive',
		],
	];

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
		];
	}
/* -----
   Les fichiers "menu de cantine" sont stockés dans le répertoire images\Menus_Cantine.
   les noms des fichiers contenant les menus de cantine doivent être 
   <année><no de mois>__<mois en alpha>.pdf
   Par exemple 202210_-_Octobre.pdf
   Ce plugin va déplacer les fichiers dont la semaine est dépassé dans le répertoire
   images\Menus_Cantine\archives
*/


	protected function autoarchive(ExecuteTaskEvent $event): int
    {
		$month = HtmlHelper::date('now', "m");
		$year = HtmlHelper::date('now', "Y");
		$dir = JPATH_SITE.'/images/Menus_Cantine';
		$files = Folder::files($dir,'2*.pdf',null ,null ,array() , array('index.html','.htaccess'));

		if (count($files) > 0) {
			foreach ($files as $file) {
			    $detail = explode("_",File::stripExt($file));
			    if ($detail[0] < $year.$month)  {
			        $this->archivefile($dir.'/'.$file,$dir.'/archive',$file);
			    }
			}
		}
		return TaskStatus::OK;
	}
	function archivefile($src,$dest,$file) {
	    if (!Folder::exists($dest)) {
	        Folder::create($dest);
	    }
	    $ret = File::move($src,$dest.'/'.$file);
	    return $ret;
	}
}