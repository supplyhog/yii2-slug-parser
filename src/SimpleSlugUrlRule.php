<?php

namespace supplyhog\SlugParser;

use yii\base\InvalidConfigException;

class SimpleSlugUrlRule extends SlugRuleBase implements \yii\web\UrlRuleInterface
{
	/**
	 * Minimum length for testing a slug
	 * Anything shorter than this is not a slug route
	 * This helps to speed up parsing as it does not have to check "most" failing routes
	 * @var int
	 */
	public $minLength = 8;

	/**
	 * The slug field on the model
	 * @var string
	 */
	public $modelField;

	/**
	 * The field on the model that will be used for the params
	 * Usually this would be the id/PrimaryKey
	 * @var string
	 */
	public $modelKey;

	public $name = 'Slug Url';

	public function init()
	{
		if(!isset($this->modelField)){
			throw new InvalidConfigException('Must have a modelField set');
		}
		if(!isset($this->modelKey)){
			throw new InvalidConfigException('Must have a modelKey set');
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
		$parts = self::getParts($request, false);
		if (count($parts) === 0) {
			return false;
		}
		$slugString = array_shift($parts);

		if (strlen($slugString) < $this->minLength) {
			return false;
		}

		$modelClass = $this->modelClass;
		$model = $modelClass::find()->andWhere([$this->modelField => $slugString])->one();

		if(!$model) {
			return false;
		}

		$params = $request->queryParams;
		$params[$this->modelKey] = $model->{$this->modelKey};

		$parts = self::getParts($request);

		$action = $this->getAction($parts);
		$params = $params + $this->partsToParams($parts);

		if(!$this->actionExists($action)){
			return false;
		}
		return ['/' . $this->route . '/' . $action, $params];
	}
}
