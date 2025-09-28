<?php
/*
Cheat sheet for building Yii2 queries both with ActiveRecord and manually
*/

# ===============================================
# USING MODELS (ActiveRecord)
# ===============================================
// http://www.yiiframework.com/doc-2.0/guide-db-active-record.html

// Returning a single record:
Customer::findOne(153);  //shortcut for: Customer::find()->one()    (does NOT automatically add LIMIT 1 to the SQL!)
								//does NOT support LIKE, IN, etc
								//and it seems like where() doesn't even do that when you want to specify conditions on multiple fields?

Customer::findOne(['age' => 30, 'status' => 1]);

// Returning all records:
Customer::findAll([77, 98, 102]);  // findAll() doesn't work without any parameters, to get all records use: Customer::find()->all()

Customer::findAll(['usr_status' => 'active']);


// Return records based on a condition in a related table
Customer::find()
	->joinWith('orders theOrders')  //LEFT JOIN  (relation name is used)
	->joinWith('orders.orderLines')  //LEFT JOIN  (relation names are used)
	->joinWith('orders.orderTags')  //LEFT JOIN  (relation names are used)
	->innerJoinWith('addresses')  //INNER JOIN  (relation name is used)
	->where(['theOrders.ord_status' => Order::STATUS_ACTIVE])   //table name not needed if column name is unique
	->all();    // http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#relational-data

// Use expressions in SELECT (here with a joined table)
Template::find()->select(["CONCAT(cat_name, ' - ', tmpl_name) AS eff_name"])->innerJoinWith('prodCategory')->all();


// Use LIKE:
Customer::find()
->andWhere(['like', 'lm_name', 'john'])  // lm_name LIKE '%john%'
->andWhere(['like', 'lm_name', ['john', 'mary']])  // lm_name LIKE '%john%' AND lm_name LIKE '%mary%'
->andWhere(['like', 'lm_name', 'john%', false])  // lm_name LIKE 'john%'
->andWhere(['like', new \yii\db\Expression('CONCAT(cont_firstname, ' ', cont_lastname)'), 'john doe'])  // CONCAT(cont_firstname, ' ', cont_lastname) LIKE '%john doe%'
->andWhere(['like', new \yii\db\Expression('CONCAT(cont_firstname, ' ', cont_lastname)'), str_replace(' ', '%', 'john doe'), false])  // CONCAT(cont_firstname, ' ', cont_lastname) LIKE '%john%doe%'
->andWhere(['not like', 'lm_name', 'john'])  // lm_name NOT LIKE '%john%'
->andWhere(['like binary', 'lm_name', 'John'])  // `lm_name` LIKE BINARY 'John'
->andWhere(['and', ['like', 'firstname', 'John'], ['like', 'lastname', 'Doe']])  // `firstname` LIKE 'John' AND `lastname` LIKE 'Doe'  (it doesn't work without the 'and' entry - and cannot be mixed with `'column' => 'value'` entries)

// Use IS NOT NULL:
->andWhere(['not', ['lm_pagenum' => null]])  // http://stackoverflow.com/questions/29796329/how-to-use-not-null-condition-in-yii2#29796691

// Use IN & NOT IN:
->andWhere(['in', 'moduleID', [1,2,3]])
->andWhere(['moduleID' => [1,2,3]])
->andWhere(['not in', 'moduleID', [1,2,3]])
->andWhere(['not', ['lm_pagenum' => [1,2,3]]])

// Use other operators:
->andWhere(['<>', 'moduleID', 10])  // moduleID <> 10  (remember that this will not include NULL values)
->andWhere(['>=', 'moduleID', 10])  // moduleID >= 10
->andWhere(['>=', 'date_begin', new \yii\db\Expression('NOW()')])  // date_begin >= NOW()
			// NOTE: the other way around won't work, then you must do it like this:
			->andWhere(['<', new \yii\db\Expression('NOW()'), new \yii\db\Expression('date_begin') ])  // without Expression() the field name will become a string!
->andWhere(['>', new \yii\db\Expression('DATEDIFF(NOW(), lay_last_access)'), 30])  // DATEDIFF(...) > 30
->andWhere(['>', new \yii\db\Expression("TIMESTAMPDIFF(MINUTE,'2003-02-01','2003-05-01 12:05:55')"), 120000])
->andWhere(['between', 'price', 100, 1000])  // price BETWEEN 100 AND 1000
->andWhere(['REGEXP', 'description', "/NB\\s*=/"])  // description REGEXP 'NB\s*='

// Use "local" OR:
->andWhere(['or', ['in', 'moduleID', [1,2,3]], ['mod_is_master' => 1]])  //= (moduleID IN (1, 2, 3) OR mod_is_master = 1)

// Complex with both OR and AND:
->andWhere([
	'or',
	['user.status' => 1],
	[
		'and',
		['product.available' => 1],
		['not', ['product.category' => 'electronics']],
	],
]);

// Use a subquery (eg. get products that have the tag 'shippable')
$tagSubQuery = Tag::find()->select('tag_productID')->where(['name' => 'shippable']);
$products = Product::find()->where(['in', 'productID', $tagSubQuery])->all();  //or 'not in'

// See https://www.yiiframework.com/doc/api/2.0/yii-db-queryinterface#where()-detail for even more

// Use manual writing of SQL:
->andWhere('BINARY ref = :ref', ['ref' => 'A-left'])  // BINARY ref = 'A-left'



// Count number of records:
Customer::find()->count();

// Limit number of records:
Customer::find()->limit(10);


// Getting the raw SQL:
Layout::find()
	->where(['lay_customerID' => 999])
	->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql;  //source: https://stackoverflow.com/questions/27389146/log-the-actual-sql-query-using-activerecord-with-yii2
	// Or just like this seems to work too:
	->where(['lay_customerID' => 999])->createCommand()->rawSql

// Find by manual SQL
Customer::findBySql("SELECT * FROM main_customers")->all();
Customer::findBySql("SELECT * FROM main_customers WHERE date_added > :ab", ['ab' => '2020-01-01'])->all();


// Delete multiple records
User::deleteAll(['usr_email' => 'tester.email@sample.com']);


# ===============================================
# USING MANUAL SQL
# ===============================================
// Documentation: https://www.yiiframework.com/doc/api/2.0/yii-db-command

$result       = \Yii::$app->db->createCommand("SELECT * FROM list_countries WHERE countryID = :id", ['id' => 47])->queryAll();
						//or: queryOne(), queryColumn(), queryScalar()  (see query() for large queries)  (has no count() method)

$affectedRows = \Yii::$app->db->createCommand("DELETE FROM main_modules")->execute();

$affectedRows = \Yii::$app->db->createCommand("DELETE FROM main_modules WHERE moduleID = :moduleID", ['moduleID' => 45])->execute();
// parameter values can't be an array - have to manually escape and insert them into the SQL string

$affectedRows = \Yii::$app->db->createCommand("ALTER TABLE main_modules AUTO_INCREMENT=0")->execute();

$affectedRows = \Yii::$app->db->createCommand("ALTER TABLE main_modules AUTO_INCREMENT=0")->rawSql;  //returns the SQL statement


(new \yii\db\Query())->select('*')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->all();
(new \yii\db\Query())->select('*')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->one();
(new \yii\db\Query())->select('*')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->column();
(new \yii\db\Query())->select('MAX(lm_order)')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->scalar();
(new \yii\db\Query())->select('MAX(lm_order)')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->count();
(new \yii\db\Query())->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->exists();
(new \yii\db\Query())->select('MAX(lm_order)')->from('main_layout_modules')->where(['lm_layoutID' => $layoutID])->createCommand()->rawSql;  //returns the SQL statement
// select() documentation: http://www.yiiframework.com/doc-2.0/yii-db-query.html#select()-detail

// INSERT
$affectedRows = \Yii::$app->db->createCommand()->insert('main_people', [
	'name' => 'Sam',
	'age' => 30,
])->execute();

// INSERT but ignore if record already exists
$updateColumns = false;  //alternatively set this to array of columns to update if you want to update instead
$affectedRows = \Yii::$app->db->createCommand()->upsert('main_people', [
	'name' => 'Sam',
	'age' => 30,
], $updateColumns)->execute();

// UPDATE (table name, column values, condition)  (condition takes same formats as where() )
$affectedRows = \Yii::$app->db->createCommand()->update('main_people', [
		'status' => 1,
	],
	'age = 30'
)->execute();

$affectedRows = \Yii::$app->db->createCommand()->update('main_people', [
		'status' => 1,
	],
	'age = :minAge', [':minAge' => $minAge]
)->execute();

// Get last inserted ID
$id = \Yii::$app->db->getLastInsertID();  //after a createCommand() INSERT query

// DELETE
$affectedRows = \Yii::$app->db->createCommand()->delete('main_modules', ['mod_templateID' => 4245])->execute();


# ===============================================
# OTHER STUFF
# ===============================================


// Quote a single value:
\Yii::$app->db->quoteValue($value);  // http://stackoverflow.com/a/27274914/2404541 -- http://www.yiiframework.com/doc-2.0/yii-db-schema.html#quoteValue()-detail


// Create transaction:
$transaction = \Yii::$app->db->beginTransaction();  // Yii2 automatically handles nested transactions by using Savepoints - otherwise it throws error if underlying DBMS doesn't support it
try {
	\Yii::$app->db->createCommand($sql1)->execute();
	\Yii::$app->db->createCommand($sql2)->execute();
	//.... other SQL executions

	// with models: http://stackoverflow.com/questions/26856663/yii2-save-related-records-in-single-save-call-in-single-transaction

	$transaction->commit();
} catch (\Exception $e) {
	$transaction->rollBack();
	throw $e;
}


// Create transaction with option for soft errors/exceptions within the try..catch:
$errors = [];
try {

	if ($somethingFailed) {
		throw new \EndUserSafeException('Invalid user status.');  //create the exception class you want to use, EndUserSafeException is just an example
	}

	$transaction->commit();
} catch (\EndUserSafeException $e) {
	$transaction->rollBack();
	$errors[] = $e->getMessage();

} catch (\Exception $e) {
	$transaction->rollBack();
	throw $e;
}



# ===============================================
# USING QUERY STRING eg. in REST API (DataFilter)
# ===============================================
// Documentation:
// - https://www.yiiframework.com/doc/guide/2.0/en/rest-filtering-collections
// - https://www.yiiframework.com/doc/guide/2.0/en/rest-resources#fields
// - https://www.yiiframework.com/doc/api/2.0/yii-data-datafilter

// Search for lastname being Smith
filter[lastname]=Smith

// Possible operators:
[and]   :  AND
[or]    :  OR
[not]   :  NOT
[lt]    :  <
[gt]    :  >
[lte]   :  <=
[gte]   :  >=
[eq]    :  =
[neq]   :  !=
[in]    :  IN
[nin]   :  NOT IN
[like]  :  LIKE

// Search for lastname containing Smith
filter[lastname][like]=Smith

// Search for address being NULL
filter[address]=NULL

// Search for address being NOT NULL
filter[not][address]=NULL

// Search for userID being either 11, 15 or 18
filter[userID][in][0]=11&filter[userID][in][1]=15&filter[userID][in][2]=18

// Search for lastname being Smith or Johnson
filter[or][0][lastname]=Smith&filter[or][1][lastname]=Johnson

// Search for lastname containing Smith or Johnson
filter[or][0][lastname][like]=Smith&filter[or][1][lastname][like]=Johnson

// Sort by firstname ascendingly (see also https://www.yiiframework.com/doc/api/2.0/yii-data-sort for defining custom sorting)
sort=firstname

// Sort by firstname descendingly
sort=-firstname

// Select fields (attributes) to return (does not affect expanded models)
fields=userID,email

// Include fields from related post if defined in extraFields() (`post` is the name of the relation which is defined by the model method `getPost()`)
expand=post

// Include fields from the role model that is related to the post that is related to our primary model (if `role` is defined in post's extraFields())
//   This way you can keep digging through the relations
expand=post.role

// Include fields both from related post AND from related addresses if defined in extraFields(). You can dig still deeper with "." as well.
expand=post,address

// Page number to retrieve when using pagination/Serializer
page=3

// Number of records to return per response when using pagination/Serializer
per-page=200
