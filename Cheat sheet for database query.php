<?php
/*
Cheat sheet for building Yii2 queries both with ActiveRecord and manually
*/

# ===============================================
# USING MODELS
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
	->joinWith('orders')  //LEFT JOIN  (relation name is used)
	->joinWith('orders.orderLines')  //LEFT JOIN  (relation names are used)
	->joinWith('orders.orderTags')  //LEFT JOIN  (relation names are used)
	->innerJoinWith('addresses')  //INNER JOIN  (relation name is used)
	->where(['main_orders.ord_status' => Order::STATUS_ACTIVE])   //table name not needed if column name is unique
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

// Use IS NOT NULL:
->andWhere(['not', ['lm_pagenum' => null]])  // http://stackoverflow.com/questions/29796329/how-to-use-not-null-condition-in-yii2#29796691

// Use IN & NOT IN:
->andWhere(['in', 'moduleID', [1,2,3]])
->andWhere(['not in', 'moduleID', [1,2,3]])

// Use other operators:
->andWhere(['<>', 'moduleID', 10])  // moduleID <> 10  (remember that this will not include NULL values)
->andWhere(['>=', 'moduleID', 10])  // moduleID >= 10
->andWhere(['>=', 'date_begin', new \yii\db\Expression('NOW()')])  // date_begin >= NOW()
->andWhere(['>', new \yii\db\Expression('DATEDIFF(NOW(), lay_last_access)'), 30])  // DATEDIFF(...) > 30
->andWhere(['>', new \yii\db\Expression("TIMESTAMPDIFF(MINUTE,'2003-02-01','2003-05-01 12:05:55')"), 120000])
->andWhere(['between', 'price', 100, 1000])  // price BETWEEN 100 AND 1000

// Use "local" OR:
->andWhere(['or', ['in', 'moduleID', [1,2,3]], ['mod_is_master' => 1]])  //= (moduleID IN (1, 2, 3) OR mod_is_master = 1)

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
	->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;  //source: https://stackoverflow.com/questions/27389146/log-the-actual-sql-query-using-activerecord-with-yii2


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
$affectedRows = \Yii::$app->db->createCommand()->delete('main_modules', ['mod_templateID' => $templ_model->templateID])->execute();


# ===============================================
# OTHER STUFF
# ===============================================


// Quote a single value:
\Yii::$app->db->quoteValue($value);  // http://stackoverflow.com/a/27274914/2404541 -- http://www.yiiframework.com/doc-2.0/yii-db-schema.html#quoteValue()-detail


// Create transaction:
$transaction = \Yii::$app->db->beginTransaction();
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
