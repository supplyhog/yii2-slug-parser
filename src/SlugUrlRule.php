<?php

namespace supplyhog\SlugParser;

class SlugUrlRule extends SlugRuleBase implements \yii\web\UrlRuleInterface
{
	/**
	 * @var CompositeSlugUrlRule
	 */
	public $group;

	public $name = 'Slug Class';

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

		$parts = self::getParts($request);

		$action = $this->getAction($parts);
		$params = $params + $this->partsToParams($parts);

		if(!$this->actionExists($action)){
			return false;
		}
		return ['/' . $this->route . '/' . $action, $params];
	}
}
