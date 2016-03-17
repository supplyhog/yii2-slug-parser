#yii2-slug-parser

##Installation
Install this extension via [composer](http://getcomposer.org/download). Add this line to your project’s composer.json

```php
supplyhog/yii2-slug-parser” : “dev-master”
```

##What Does It Do?

Lets you have slugs that are defined via an interface (allowing for a universal slug table) so that url.com/post-title-slug
will correctly parse into say /post/view?id=23. With the ```SecondSlugInterface``` you can also have a second slug
say /john-doe/post-title-slug parse into /post/view?id=23 while /sarah-jane/post-title-slug parses into /post/view?id=47. 
If you use ```SimpleSlugUrlRule``` it will handle slugs that are on a table.

In addition the slugs can have action and params appended in the friendly url as shown in the examples.

###Example Urls and How They are Parsed
 - /post-title-slug -> /post/view?id=23
 - /john-doe -> /author/view?id=51
 - ( ^ Note You can cascade to another model with multiple rules)
 - /john-doe/draft/2 -> /author/view?id=51&draft=2
 - ( ^ Note Pairs after the slug parse into query params)
 - /post-title-slug/edit -> /post/edit?id=23
 - ( ^ Note Single url pieces after the slug can be a different action)
 - /post-title-slug/edit?draft=1 -> /post/edit?id=23&draft=1
 - /post-title-slug/edit/draft/1 -> /post/edit?id=23&draft=1
 - ( ^ Note Single url piece after the slug can be a different action and still use pairs for query params)
 - /john-doe/post-title-slug -> /post/view?id=23
 - /sarah-jane/post-title-slug -> /post/view?id=47
 - ( ^ Note that with secondary slug, they can be the same as long as the first slug is unique to the second model)
 - /john-doe/tag-slug -> /post/tag?id=82
 - ( ^ Note You can cascade to another model with multiple rules)
 - /john-doe/post-title-slug/edit?draft=1 -> /post/edit?id=23&draft=1
 - /john-doe/post-title-slug/edit/draft/1 -> /post/edit?id=23&draft=1
 
##What Does It NOT Do?

 - It will not create the Urls for you.
 - It does not have a slug model for you, only an interface.
 - It will not make breakfast.

Why does it not handle url creation as well? So that the slugs can be permalinks allowing for multiple slugs to point 
 at the same parsed url.

##Setup

###Configuration

```php
//add url rules in config/main.php for the appropriate application
$config[‘urlManager’]['rules'][] = [
  'class' => 'supplyhog\SlugParser\CompositeSlugUrlRule',
  //'minLength' => 8 //Set the minimum require length of the slug default 8 
  'slugClass' => 'common\models\Slug', //The class that impliments the SlugInterface
  'rules' => [
    //Example Rule for a top level slug
    // http://site.com/post-title-slug
    [
      'class' => 'supplyhog\SlugParser\SlugUrlRule',
      'modelClass' => 'common\models\Post', //The class for the slug
      'controller' => 'frontend\controllers\PostController', //The controller the slug is pointing at
      'route' => 'post', //The non-slug route
      //'defaultAction' = 'view', //The default action if nothing else is used. 
    ],
    //Example Rule for a second-level slug
    // http://site.com/author-slug/post-title-slug
    [
      'class' => 'supplyhog\SlugParser\SecondSlugUrlRule',
      'secondModelClass' => 'common\models\Post', //The class of second slug (post-title-slug)
      'controller' => 'frontend\controllers\PostController', //The controller for the view
      'firstModelClass' => 'common\models\Author', //This is the class for the
      'route' => 'post', //The non-slug route
      //'defaultAction' = 'view', //The action used.
    ],
    //Additional example Rule for a top level slug that you might use in conjunction with the previous one
    // http://site.com/author-slug
    [
      'class' => 'supplyhog\SlugParser\SlugUrlRule',
      'model' => 'common\models\Author', //The class for the slug
      'controller' => 'frontend\controllers\AuthorController', //The controller the slug is pointing at
      'route' => 'author', //The non-slug route
    ],
  ]
];

//SimpleSlugUrlRule For when the slug is not in a separate table
$config[‘urlManager’]['rules'][] = [
  'class' => 'supplyhog\SlugParser\SimpleSlugUrlRule',
  //'minLength' => 8 //Set the minimum require length of the slug default 8
  'modelClass' => 'common\models\Post', //The class that has the slug field
  'controller' => 'frontend\controllers\PostController', //The controller the slug is pointing at
  'route' => 'post', //The non-slug route
  //'defaultAction' = 'view', //The default action if nothing else is used.
  'modelField' => 'slug', //The permalink/slug field on the model that can be found using ::find()->andWhere([modelField => slugValue])
  'modelKey' => 'id', //The field on the model that will be added to the params. Usually an id/primary key  
];

```

###SlugInterface

#### Suggested Schema
 - slug
 - model_class
 - model_key
 - model_value

####```public static function findBySlug($slug)```
 - ```$slug``` is the text in the first url position
Returns null or the slug object that implements the SlugInterface.

####```public function getModelKey()```
Returns the key for the parameter. So if the normal url is /post/view?id=47 this should return "id".

####```public function getModelValue()```
Returns the value for the parameter. So if the normal url is /post/view?id=47 this should return 47.

####```public function getModelClass()```
Returns the class of the model for this slug. Used to validate the correct rule in the chain.

###SecondSlugInterface extends SlugInterface

####```public function getSecondSlugKeyValue($secondModelSlug, $secondModelClass)```
 - ```$secondModelSlug``` is the text in the second url position
 - ```$secondModelClass``` is the class of the object it points at for validation if multiple rules
Returns an array ```[key => value]``` that will be appended to the query params. 
So if the normal url is /post/view?id=47 this should return ```[id => 47]```.
