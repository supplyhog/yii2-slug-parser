<?php

namespace supplyhog\SlugParser;

use Yii;
use yii\web\CompositeUrlRule;
use yii\base\InvalidConfigException;
use yii\web\UrlRuleInterface;

class CompositeSlugUrlRule extends CompositeUrlRule
{
	/**
	 * Minimum length for testing a slug
	 * Anything shorter than this is not a slug route
	 * This helps to speed up parsing as it does not have to check "most" failing routes
	 * @var int
	 */
	public $minLength = 8;

	/**
	 * Slug Class that Implements \supplyhog\SlugParser\SlugInterface
	 * @var string
	 */
	public $slugClass;

	/**
	 * Store the slug once found
	 * @var SlugInterface
	 */
	protected $_slugModel;

	/**
	 * Store the slug once found
	 * @var string
	 */
	protected $_slugString;

	/**
	 * Subrules
	 * @var array
	 */
	public $rules = [];

	public function init()
	{
		parent::init();
		if(!isset($this->slugClass)){
			throw new InvalidConfigException('Please set the slugClass to a model that implements \supplyhog\SlugParser\SlugInterface');
		}
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
		$parts = $this->getParts($request, false);
		if (count($parts) === 0) {
			return false;
		}
		$this->_slugString = array_shift($parts);

		if (strlen($this->_slugString) < $this->minLength) {
			return false;
		}
		$slugClass = $this->slugClass;
		$slug = $slugClass::findBySlug($this->_slugString);
		if (!$slug) {
			return false;  // this rule does not apply
		}

		$this->_slugModel = $slug;

		return parent::parseRequest($manager, $request);
	}

	/**
     * @inheritdoc
     */
    protected function createRules()
    {
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!is_array($rule)) {
                throw new InvalidConfigException('Subs of the SlugUrlRule must be defined as arrays.');
            }
			$rule['group'] = $this;
            $rule = Yii::createObject($rule);
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $rules[] = $rule;
        }
        return $rules;
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

	/**
	 * @return SlugInterface
	 */
	public function getSlug()
	{
		return $this->_slugModel;
	}

	/**
	 * @return string
	 */
	public function getSlugString()
	{
		return $this->_slugString;
	}

	/**
	 * @param \yii\web\Request $request
	 * @param bool $shift
	 * @return array
	 */
	public function getParts($request, $shift = true)
	{
		$pathInfo = $request->getPathInfo();
		$parts = array_values(array_filter(explode('/', trim($pathInfo))));
		if(!$shift){
			return $parts;
		}
		//Shift off the slug
		array_shift($parts);
		return $parts;
	}
}
