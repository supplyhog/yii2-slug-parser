<?php

namespace supplyhog\SlugParser;

use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * Class SecondSlugUrlRule
 * To use this, CompositeSlugUrlRule->getSlug() must return SecondSlugInterface
 * @package supplyhog\SlugParser
 */
class SecondSlugUrlRule extends SlugUrlRule
{
	/**
	 * The secondModelClass MUST implement \supplyhog\SlugParser\SecondSlugInterface
	 * @var string
	 */
	public $secondModelClass;

	public $name = 'Second Slug Class';

	public function init()
	{
		if(!isset($this->secondModelClass)){
			throw new InvalidConfigException('Must have a secondModelClass set');
		}
		parent::init();
	}

	/**
	 * Parses the given request and returns the corresponding route and parameters.
	 * @param UrlManager $manager the URL manager
	 * @param \yii\web\Request $request the request component
	 * @return array|boolean the parsing result. The route and the parameters are returned as an array.
	 * If false, it means this rule cannot be used to parse this path info.
	 */
	public function parseRequest($manager, $request)
	{
		$slug = $this->group->getSlug();
		/* @var $slug \supplyhog\SlugParser\SecondSlugInterface */

		if ($slug->getModelClass() !== $this->modelClass) {
			return false;
		}

		$parts = $this->group->getParts($request);

		//If there are no parts, we cannot continue!
		if(!$parts){
			return false;
		}

		//Wait until here to test as it might fail before this matters.
		if( !($slug instanceof SecondSlugInterface) ) {
			return false;
		}

		$secondModelSlug = array_shift($parts);

		$secondSlugParams = $slug->getSecondSlugKeyValue($secondModelSlug, $this->secondModelClass);
		$action = $this->getAction($parts);

		//verify that the action requested is actually an action on the controller
		if(!$secondSlugParams || !$this->actionExists($action)){
			return false;
		}

		//Params from the secondSlug (likely all there is), parts of the url, and the actual queryParams get together
		$params = $secondSlugParams + $this->partsToParams($parts) + $request->queryParams;

		return ['/' . $this->route . '/' . $action, $params];
	}
}
