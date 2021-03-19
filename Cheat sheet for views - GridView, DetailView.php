<?php
/*
===============================================================================================================
GridView
===============================================================================================================
*/

// Customize value
			[
				'attribute' => 'lay_template_docID',
				'value' => function($model) {
					return $model->templateDoc->template->tmpl_name .' '. $model->templateDoc->tdoc_paper_size;
				},
			],

// Customized content
			[
				'attribute' => 'lay_num_of_pages',
				'content' => function($model, $key, $index, $column) {
					return $model->lay_num_of_pages_read;
				},
			],

// Custom HTML with links
			[
				'attribute' => 'layoutID',
				'format' => 'html',
				'value' => function($model) {
					$html = $model->layoutID;
					$html .=  '<div><a href="'. \yii\helpers\Url::to(['layout/preparing', 'id' => $model->layoutID, 'id2' => 54]) .'">Final output</a></div>';
					if (YII_ENV == 'dev') {
						$html .=  '<div><a href="'. \yii\helpers\Url::to(['system/menu', 'layoutID' => $model->layoutID]) .'">Menu</a></div>';
					}
					return $html;
				},
			],

// Filter: Dropdown based on `allowedValues`
			[
				'attribute' => 'lay_status',
				'filter' => Html::activeDropDownList($searchModel, 'lay_status', $searchModel::allowedValues('lay_status'), ['prompt' => '', 'class' => 'form-control', 'style' => 'width: 85px']),
			],

// Filter and Value: Dropdown based on `allowedValues`
			[
				'attribute' => 'lay_status',
				'filter' => Html::activeDropDownList($searchModel, 'lay_status', $searchModel::allowedValues('lay_status'), ['prompt' => '', 'class' => 'form-control', 'style' => 'width: 85px']),
				'value' => function($model) {
					return $model::allowedValues('lay_status')[ $model->lay_status ];
				}
			],

// Filter: Dropdown based on a separate model query
			[
				'attribute' => 'customer.cust_name',  //lay_customerID
				'filter' => Html::activeDropDownList($searchModel, 'lay_customerID', ArrayHelper::map(\app\models\Customer::find()->orderBy('cust_name')->all(), 'customerID', 'cust_name'), ['prompt' => '', 'class' => 'form-control']),
				// with multiple attributes in the label value:
				'filter' => Html::activeDropDownList($searchModel, 'lay_customerID', ArrayHelper::map(\app\models\Customer::find()->orderBy('cust_name')->all(), 'customerID', function($model) { return $model->cust_name .' ('. $model->customerID .')'; }), ['prompt' => '', 'class' => 'form-control']),
			],

/*
===============================================================================================================
DetailView
===============================================================================================================
*/

// Customize value for attribute
			[
				'attribute' => 'lay_num_of_pages',
				'value' => function($model, $widget) {
					return $model->lay_num_of_pages_read;
				},
				'format' => 'html',  //already HTML encoded
			],

// Add value not based on an attribute
			[
				'label' => 'Access Code',
				'value' => $model->someCode .' &nbsp;&nbsp;(<a href="'. Url::to(['site/login', 'trial' => 'mcq2mYTzYAx'], true) .'">Link</a>)',
				'format' => 'html',  //already HTML encoded
			],

// Get value from `allowedValues`
			[
				'attribute' => 'ev_is_active',
				'value' => $model::allowedValues('ev_is_active')[ $model->ev_is_active ],
			],
			\winternet\yii2\DetailViewHelper::fromAllowedValues($model, 'usr_enable_beta'),

// Static yes/no
			[
				'attribute' => 'tdoc_active',
				'value' => function($model, $widget) {
					return ($model->tdoc_active ? Yii::t('app', 'Yes') : Yii::t('app', 'No'));
				},
			],

// Multiple lines
			'tdoc_php_code:ntext',
