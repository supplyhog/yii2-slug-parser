<?php

namespace supplyhog\SlugParser;

use yii\base\Object;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;

class SlugUrlRule extends Object implements \yii\web\UrlRuleInterface
{
	/**
	 * @var CompositeSlugUrlRule
	 */
	public $group;

	/**
	 * Maps modelClass
	 * @var string
	 */
	public $modelClass;

	/**
	 * Which controller? We want to validate that the action exists on it.
	 * @var string
	 */
	public $controller;

	/**
	 * The route to the controller
	 * @var string
	 */
	public $route;

	/**
	 * The default action to use. Yes you set once for them all.
	 * @var string
	 */
	public $defaultAction = 'view';
	public $suffix = null;

	public $name = 'Slug Class';

	public function init()
	{
		if(!isset($this->modelClass)){
			throw new InvalidConfigException('Must have a modelClass set');
		}
		if(!isset($this->controller)){
			throw new InvalidConfigException('Must have a controller set');
		}
		if(!isset($this->route)){
			throw new InvalidConfigException('Must have a route set');
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

		if ($slug->getModelClass() !== $this->modelClass) {
			return false;
		}

		$params = $request->queryParams;
		$params[$slug->getModelKey()] = $slug->getModelValue();

		$parts = $this->group->getParts($request);

		$action = $this->getAction($parts);
		$params = $params + $this->partsToParams($parts);

		if(!$this->actionExists($action)){
			return false;
		}
		return ['/' . $this->route . '/' . $action, $params];
	}

	/**
	 * Takes pairs of url parts and turns them into query params
	 * Example: /slug-is-here/param/key === /slug-is-here?param=key
	 * @param array &$parts
	 * @return array
	 */
	protected function partsToParams(&$parts)
	{
		$params = [];
		while ($parts && count($parts) > 1) {
			$params[array_shift($parts)] = array_shift($parts);
		}
		return $params;
	}

	/**
	 * Retreve the action from the url if there is an odd number of parts left
	 * @param array &$parts
	 * @return string
	 */
	protected function getAction(&$parts)
	{
		$partCount = count($parts);
		if ($partCount > 0 && $partCount % 2 !== 0) {
			return array_shift($parts);
		}
		return $this->defaultAction;
	}

	/**
	 * Verify that the action requested is actually an action on the controller
	 * @param string $action
	 * @return bool
	 */
	protected function actionExists($action)
	{
		try{
			$reflection = new \ReflectionClass($this->controller);

			if($reflection->getMethod('action'. Inflector::camelize($action))){
				return true;
			}
		}catch (\ReflectionException $e){
			//method does not exist
			return false;
		}
		return false;
	}

	/**
	 * Currently the Slug Parser does NOT support creating urls
	 * @param $manager
	 * @param $route
	 * @param $params
	 * @return bool
	 */
	public function createUrl($manager, $route, $params)
	{
		return false;
	}
}
