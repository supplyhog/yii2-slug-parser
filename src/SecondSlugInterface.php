<?php

namespace supplyhog\SlugParser;

/**
 * Interface SecondSlugInterface
 *
 * Note: The SecondSlug model is possible polymorphic, so that multiple models can use the same
 * slug position without collisions.
 *
 */
interface SecondSlugInterface extends SlugInterface {

	/**
	 * Returns an array with the key value for the second slug that will be used in the route
	 * Example:
	 *  Slug Route: /john-doe/cool-post-title
	 *  Normal Route: /post/view?id=23
	 *  getSecondSlugKeyValue(cool-post-title, \common\models\Post)
	 *   Returns [ id => 23 ]
	 *
	 * This should take into account what the slug knows about the first slug as well.
	 * This then allows for these to both be valid:
	 *  - /john-doe/cool-post-title
	 *  - /sarah-white/cool-post-title
	 * @param $secondModelSlug
	 * @param $secondModelClass
	 * @return [key => value] | null
	 */
	public function getSecondSlugKeyValue($secondModelSlug, $secondModelClass);
}