<?php
// ========================================================================================================================================
// Most validators, including inline validator, does not validate empty values unless you specifically ask it to!
// Source: http://www.yiiframework.com/doc-2.0/guide-input-validation.html#creating-validators
// 		   http://www.yiiframework.com/doc-2.0/guide-input-validation.html#handling-empty-inputs

[['bk_amount_to_pay', 'bk_amount_received', 'bk_payment_date', 'bk_payment_method'], function($attribute, $params, $validator) {
	if (!$this->$attribute && $this->bk_invoice_no) {
		$validator->addError($this, $attribute, 'Cannot change payment details because booking has been invoiced. If you need to, undo payment first.');  //SKIPPED-TRANSL
	}
}, 'skipOnEmpty' => false],



// ========================================================================================================================================
// Remember to ensure scalar or specify column name for findOne() and findAll()  - (this would be exploitable: Post::findOne(Yii::$app->request->get('id')) )
// and that column names are not escaped by where() and filterWhere()

// So do either one in case it's not certain it is a scaler (Yii ensures that parameters for controller action are always scalar)
$model = Post::findOne((int) Yii::$app->request->get('id'));
$model = Post::findOne(['id' => Yii::$app->request->get('id')]);



// ========================================================================================================================================
// Remember to consider the parent beforeSave() as well

public function beforeSave($isInsert) {
	if (!parent::beforeSave($isInsert)) {
		return false;
	}

	// ...

	return true;
}



// ========================================================================================================================================
// Method of controlling which users can add/delete/update records (but only if there are no conditions and exceptions)

public function beforeSave($isInsert) {
	if ($isInsert && $this->getScenario() == TemplateDoc::SCENARIO_TDOC_ADMIN) {
		\Yii::$app->system->error('You are not allowed to add records.', null, ['register' => false]);
		// - or -
		$this->addError('bk_firstname', 'You are not allowed to add records.');
	}

	return parent::beforeSave($isInsert);
}

//  - or -

public function beforeValidate() {
	if (1) {
		$this->addError('bk_firstname', 'Cannot change record because event has been marked inactive.');
	}

	return parent::beforeValidate();
}



// ========================================================================================================================================
// afterSave()'s argument $changedOldAttributes only contains *changed* attributes (their OLD values)

public function afterSave($isInsert, $changedOldAttributes) {
	// Make a complete set of old attributes
	$oldValues = array_merge($this->toArray(), $changedOldAttributes);

	return parent::afterSave($isInsert, $changedOldAttributes);
}



// ========================================================================================================================================
// Remember to consider the parent beforeDelete() as well - and with option to specify custom error messages
//       NOTE: when calling model->delete() you just check for error messages on the model if it returns false.

public function beforeDelete() {
	if (!parent::beforeDelete()) {
		$this->addError('contactID', 'For some unknown reason we could not delete this record.');
	}

	// Relational restrictions
	if (!empty($this->getTaskMentions()->count() > 0)) {
		$this->addError('contactID', Yii::t('app', 'Customer has at least one layout.'));
	}

	// Other custom restrictions
	if (someOtherCheckFails) {
		$this->addError('contactID', 'You may not delete this record as it is more than two months old.');
	}

	return ($this->hasErrors() ? false : true);
}



// ========================================================================================================================================
// Remember to return boolean in model methods like beforeValidate() and beforeSave(), otherwise calling save() may return false but no mention of error reasons whatsoever! (see You are probably using an event in your model that doesn't return true. eg. public function beforeSave() {....... return true;//must return true after everything})
// Remember to do return in controller actions!

// ========================================================================================================================================
// Labels on ActiveForm form fields are automatically HTML encoded, unless you set it specifically like this:

$form->field($booking, 'bk_accept_terms')->checkbox()->label($booking->attributeLabels()['bk_accept_terms']);

// ========================================================================================================================================
// Removing an item from an array (example removes c_customerID from the array)

$newArray = array_diff($oldArray, ['c_customerID']);
