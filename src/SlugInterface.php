<?php

namespace supplyhog\SlugParser;

/**
 * Interface SlugInterface
 *
 * Note: the Slug model is polymorphic, so that multiple models can use the same
 * slug reference without collisions. If you do not need the polymorphism, just
 * set the string outputs of getModelKey and getModelClass.
 *
 * The Slug Model can be created in your preferred method.
 * Suggested Schema includes
 *  - slug
 *  - model_class (if using multiple models)
 *  - model_key (if using multiple models)
 *  - model_value
 *
 */
interface SlugInterface {

	/**
	 * Find the model by slug
	 * @param $slug
	 * @return \yii\base\Model | null
	 */
	public static function findBySlug($slug);

	/**
	 * Returns the key for the value that can be used to uniquely identify the row
	 * Normally this would be the field for the primaryKey (model_key in the suggested schema)
	 * Example:
	 * Normal Url: /post/view?id=47
	 * This should return "id"
	 * @return string
	 */
	public function getModelKey();

	/**
	 * Returns the value that can be used to uniquely identify the row
	 * Normally this would be the primaryKey value (model_value in the suggested schema)
	 * Example:
	 * Normal Url: /post/view?id=47
	 * This should return 47
	 * @return string
	 */
	public function getModelValue();

	/**
	 * Returns the class of the model for this slug.
	 * (model_class in the suggested schema)
	 * Used to validate the correct rule
	 * @return string
	 */
	public function getModelClass();
}