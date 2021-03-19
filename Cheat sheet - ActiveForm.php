<?php
/*
===============================================================================================================
_form.php
===============================================================================================================

$editAttribs below is an array of attributes (from safeAttributes()) that user is allowed to edit.
$viewAttribs below is an array of attributes that user is allowed to see.
*/

// Drop-down list with model
if (in_array('cvpu_customerID', $viewAttribs)) {
	echo $form->field($model, 'cvpu_customerID')
		->dropDownList(ArrayHelper::map(Customer::findOfUser(), 'customerID', 'cust_name'), ['disabled' => !in_array('cvpu_customerID', $editAttribs) ])
		->hint($hints['cvpu_customerID']);
}

// Drop-down list with model - with customization
if (in_array('cvpu_contacttypeID', $viewAttribs)) {
	echo $form->field($model, 'cvpu_contacttypeID')
		->dropDownList(ArrayHelper::map(ContactType::findMine()->andWhere(['conty_customerID' => $model->cvpu_customerID])->all(), 'contacttypeID', 'conty_name'), ['disabled' => !in_array('cvpu_contacttypeID', $editAttribs) ])
		->hint($hints['cvpu_contacttypeID']);
}

// Drop-down list with allowedValues() from model
if (in_array('cust_type', $viewAttribs)) {
	echo $form->field($model, 'cust_type')
		->dropDownList(Customer::allowedValues('cust_type'), ['disabled' => !in_array('cust_type', $editAttribs) ])
		->hint($hints['cust_type']);
}

/*
===============================================================================================================
_search.php
===============================================================================================================
*/

// Add empty entry in beginning of array
echo $form->field($model, 'cvpu_customerID')
	->dropDownList(['' => ''] + ArrayHelper::map(Customer::findOfUser(), 'customerID', 'cust_name'));
